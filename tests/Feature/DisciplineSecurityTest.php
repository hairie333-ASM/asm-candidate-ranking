<?php

namespace Tests\Feature;

use App\Models\Candidate;
use App\Models\Discipline;
use App\Models\DueDiligenceCategory;
use App\Models\RankingExercise;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisciplineSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected Discipline $discipline1;

    protected Discipline $discipline2;

    protected User $voter1;

    protected User $voter2;

    protected RankingExercise $exercise;

    protected DueDiligenceCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create 2 Disciplines
        $this->discipline1 = Discipline::create([
            'discipline_name' => 'BAES - Biological Agriculture and Environmental Sciences',
            'description' => 'Discipline 1',
            'display_order' => 1,
            'active' => true,
        ]);

        $this->discipline2 = Discipline::create([
            'discipline_name' => 'CS - Chemical Sciences',
            'description' => 'Discipline 2',
            'display_order' => 2,
            'active' => true,
        ]);

        // 2. Create Active Exercise
        $this->exercise = RankingExercise::create([
            'exercise_name' => 'ASM Fellowship Ranking Cycle 2026',
            'description' => 'Active evaluation cycle',
            'status' => 'Open',
            'start_datetime' => now()->subDays(1),
            'end_datetime' => now()->addDays(7),
            'allow_resubmission' => false,
        ]);

        // 3. Create Due Diligence Category
        $this->category = DueDiligenceCategory::create([
            'name' => 'Academic & Scientific Integrity',
            'description' => 'Ethics and integrity review',
            'display_order' => 1,
            'active' => true,
        ]);

        // 4. Create Voters
        $this->voter1 = User::create([
            'name' => 'Voter Discipline 1',
            'email' => 'voter1@example.test',
            'password' => bcrypt('password'),
            'role' => 'voting_user',
            'discipline_id' => $this->discipline1->id,
            'active' => true,
        ]);

        $this->voter2 = User::create([
            'name' => 'Voter Discipline 2',
            'email' => 'voter2@example.test',
            'password' => bcrypt('password'),
            'role' => 'voting_user',
            'discipline_id' => $this->discipline2->id,
            'active' => true,
        ]);
    }

    /**
     * Test 1: Voting User can rank candidates in their assigned discipline.
     */
    public function test_voting_user_can_view_and_rank_candidates_in_assigned_discipline(): void
    {
        // Create 3 candidates for Discipline 1
        $c1 = Candidate::create([
            'discipline_id' => $this->discipline1->id,
            'candidate_name' => 'Candidate 1A',
            'organisation' => 'Universiti Malaya',
            'display_order' => 1,
            'active' => true,
        ]);
        $c2 = Candidate::create([
            'discipline_id' => $this->discipline1->id,
            'candidate_name' => 'Candidate 1B',
            'organisation' => 'Universiti Sains Malaysia',
            'display_order' => 2,
            'active' => true,
        ]);
        $c3 = Candidate::create([
            'discipline_id' => $this->discipline1->id,
            'candidate_name' => 'Candidate 1C',
            'organisation' => 'Universiti Kebangsaan Malaysia',
            'display_order' => 3,
            'active' => true,
        ]);

        // Access ranking page
        $response = $this->actingAs($this->voter1)->get(route('ranking.index'));
        $response->assertStatus(200);
        $response->assertSee('Candidate 1A');

        // Submit valid ranking 1..3
        $rankings = [
            $c1->id => 1,
            $c2->id => 2,
            $c3->id => 3,
        ];

        $postResponse = $this->actingAs($this->voter1)->post(route('ranking.submit'), [
            'rankings' => $rankings,
        ]);

        $postResponse->assertRedirect(route('my-submission'));
        $this->assertDatabaseHas('ranking_submissions', [
            'user_id' => $this->voter1->id,
            'discipline_id' => $this->discipline1->id,
            'exercise_id' => $this->exercise->id,
            'status' => 'SUBMITTED',
        ]);
        $this->assertDatabaseHas('rankings', [
            'candidate_id' => $c1->id,
            'ranking_number' => 1,
        ]);
    }

    /**
     * Test 2: CRITICAL SECURITY RULE - Voter attempting to rank candidates from another discipline gets HTTP 403 Forbidden!
     */
    public function test_voting_user_cannot_rank_candidate_from_different_discipline(): void
    {
        // Candidate in Discipline 1
        $c1 = Candidate::create([
            'discipline_id' => $this->discipline1->id,
            'candidate_name' => 'Candidate 1A',
            'organisation' => 'UM',
            'display_order' => 1,
            'active' => true,
        ]);

        // Candidate in Discipline 2 (Forbidden for Voter 1)
        $c2 = Candidate::create([
            'discipline_id' => $this->discipline2->id,
            'candidate_name' => 'Candidate 2A',
            'organisation' => 'UTM',
            'display_order' => 1,
            'active' => true,
        ]);

        // Attempting to submit ranking with a candidate belonging to Discipline 2
        $tamperedRankings = [
            $c1->id => 1,
            $c2->id => 2,
        ];

        $response = $this->actingAs($this->voter1)->post(route('ranking.submit'), [
            'rankings' => $tamperedRankings,
        ]);

        // MUST be 403 Forbidden
        $response->assertStatus(403);

        // Assert audit log was recorded
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->voter1->id,
            'action' => 'Security Violation: Attempted Cross-Discipline Ranking',
        ]);

        // Assert no submission was saved
        $this->assertDatabaseMissing('ranking_submissions', [
            'user_id' => $this->voter1->id,
        ]);
    }

    /**
     * Test 3: Voting user CAN search candidates in other disciplines (Cross-Discipline search allowed).
     */
    public function test_voting_user_can_search_candidates_across_all_disciplines(): void
    {
        $c2 = Candidate::create([
            'discipline_id' => $this->discipline2->id,
            'candidate_name' => 'Dr. Chemical Specialist',
            'organisation' => 'Universiti Teknologi Malaysia',
            'display_order' => 1,
            'active' => true,
        ]);

        // Voter 1 searches Discipline 2
        $response = $this->actingAs($this->voter1)->get(route('candidates.index', [
            'discipline_id' => $this->discipline2->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Dr. Chemical Specialist');
    }

    /**
     * Test 4: Voting user CAN view candidate information via API in another discipline.
     */
    public function test_voting_user_can_view_candidate_info_in_another_discipline(): void
    {
        $c2 = Candidate::create([
            'discipline_id' => $this->discipline2->id,
            'candidate_name' => 'Prof. Other Discipline',
            'organisation' => 'Universiti Putra Malaysia',
            'display_order' => 1,
            'active' => true,
        ]);

        $response = $this->actingAs($this->voter1)->get(route('api.candidates.show', $c2));
        $response->assertStatus(200);
        $response->assertJsonFragment(['candidate_name' => 'Prof. Other Discipline']);

        // Direct candidate show route redirects to due diligence
        $redirectResponse = $this->actingAs($this->voter1)->get(route('candidates.show', $c2));
        $redirectResponse->assertRedirect(route('due-diligence.index'));
    }

    /**
     * Test 5: Voting user CAN submit Due Diligence for a candidate in another discipline.
     */
    public function test_voting_user_can_submit_due_diligence_for_candidate_in_another_discipline(): void
    {
        $c2 = Candidate::create([
            'discipline_id' => $this->discipline2->id,
            'candidate_name' => 'Prof. Other Discipline Nominee',
            'organisation' => 'UPM',
            'display_order' => 1,
            'active' => true,
        ]);

        // Voter 1 submits Due Diligence for Discipline 2 candidate
        $response = $this->actingAs($this->voter1)->post(route('due-diligence.store'), [
            'candidate_id' => $c2->id,
            'category_id' => $this->category->id,
            'comment' => 'Excellent international standing and impeccable research ethics.',
            'reference_1_name' => 'Prof. Dr. Reference One',
            'reference_1_designation' => 'Dean',
            'reference_1_organisation' => 'UPM',
            'reference_1_contact_number' => '+60123456789',
            'reference_1_email' => 'ref1@upm.edu.my',
        ]);

        $response->assertRedirect(route('due-diligence.index'));
        $this->assertDatabaseHas('due_diligence_submissions', [
            'candidate_id' => $c2->id,
            'user_id' => $this->voter1->id,
            'category_id' => $this->category->id,
        ]);
    }
}
