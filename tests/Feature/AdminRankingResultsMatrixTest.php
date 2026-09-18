<?php

namespace Tests\Feature;

use App\Models\Candidate;
use App\Models\Discipline;
use App\Models\Ranking;
use App\Models\RankingExercise;
use App\Models\RankingSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRankingResultsMatrixTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Discipline $discipline;

    protected RankingExercise $exercise;

    protected Candidate $candidateA;

    protected Candidate $candidateB;

    protected User $voter1;

    protected User $voter2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'administrator',
            'active' => true,
        ]);

        $this->discipline = Discipline::create([
            'discipline_name' => 'MHS - Medical and Health Sciences',
            'description' => 'Medical and Health Sciences discipline',
            'display_order' => 1,
            'active' => true,
        ]);

        $this->exercise = RankingExercise::create([
            'exercise_name' => 'ASM Candidate Ranking Exercise 2026',
            'description' => 'Annual evaluation exercise',
            'status' => 'Open',
            'start_datetime' => now()->subDays(2),
            'end_datetime' => now()->addDays(10),
            'active' => true,
        ]);

        $this->candidateA = Candidate::create([
            'discipline_id' => $this->discipline->id,
            'candidate_name' => 'Professor Dr Gan Shiaw Sze',
            'candidate_title' => 'Professor Dr',
            'organisation' => 'Universiti Malaya',
            'sub_discipline' => 'Internal Medicine > Clinical Haematology',
            'display_order' => 1,
            'active' => true,
        ]);

        $this->candidateB = Candidate::create([
            'discipline_id' => $this->discipline->id,
            'candidate_name' => 'Professor Dr Tan Sri Lim',
            'candidate_title' => 'Professor Dr Tan Sri',
            'organisation' => 'Universiti Kebangsaan Malaysia',
            'sub_discipline' => 'Paediatrics & Child Health',
            'display_order' => 2,
            'active' => true,
        ]);

        $this->voter1 = User::factory()->create([
            'name' => 'Emeritus Professor Voter 1',
            'email' => 'voter1@asm.org.my',
            'discipline_id' => $this->discipline->id,
            'role' => 'voter',
            'active' => true,
        ]);

        $this->voter2 = User::factory()->create([
            'name' => 'Professor Voter 2',
            'email' => 'voter2@asm.org.my',
            'discipline_id' => $this->discipline->id,
            'role' => 'voter',
            'active' => true,
        ]);

        // Voter 1 ranks Candidate A as 1, Candidate B as 2
        $sub1 = RankingSubmission::create([
            'user_id' => $this->voter1->id,
            'discipline_id' => $this->discipline->id,
            'exercise_id' => $this->exercise->id,
            'status' => 'SUBMITTED',
            'submitted_at' => now()->subHours(5),
        ]);
        Ranking::create([
            'submission_id' => $sub1->id,
            'user_id' => $this->voter1->id,
            'exercise_id' => $this->exercise->id,
            'discipline_id' => $this->discipline->id,
            'candidate_id' => $this->candidateA->id,
            'ranking_number' => 1,
        ]);
        Ranking::create([
            'submission_id' => $sub1->id,
            'user_id' => $this->voter1->id,
            'exercise_id' => $this->exercise->id,
            'discipline_id' => $this->discipline->id,
            'candidate_id' => $this->candidateB->id,
            'ranking_number' => 2,
        ]);

        // Voter 2 ranks Candidate A as 2, Candidate B as 1
        $sub2 = RankingSubmission::create([
            'user_id' => $this->voter2->id,
            'discipline_id' => $this->discipline->id,
            'exercise_id' => $this->exercise->id,
            'status' => 'SUBMITTED',
            'submitted_at' => now()->subHours(2),
        ]);
        Ranking::create([
            'submission_id' => $sub2->id,
            'user_id' => $this->voter2->id,
            'exercise_id' => $this->exercise->id,
            'discipline_id' => $this->discipline->id,
            'candidate_id' => $this->candidateA->id,
            'ranking_number' => 2,
        ]);
        Ranking::create([
            'submission_id' => $sub2->id,
            'user_id' => $this->voter2->id,
            'exercise_id' => $this->exercise->id,
            'discipline_id' => $this->discipline->id,
            'candidate_id' => $this->candidateB->id,
            'ranking_number' => 1,
        ]);
    }

    public function test_admin_can_view_ranking_results_with_vertical_view_components(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.ranking-results.index', [
            'discipline_id' => $this->discipline->id,
            'exercise_id' => $this->exercise->id,
        ]));

        $response->assertStatus(200);

        // Verify Header and View Switcher
        $response->assertSee('Aggregated Candidate Preference Standings');
        $response->assertSee('Vertical View');
        $response->assertSee('Horizontal Matrix');

        // Verify Candidate Standings Leaderboard (Vertical Section A)
        $response->assertSee('Candidate Standings Leaderboard');
        $response->assertSee('Professor Dr Gan Shiaw Sze');
        $response->assertSee('Professor Dr Tan Sri Lim');
        $response->assertSee('Universiti Malaya');
        $response->assertSee('Universiti Kebangsaan Malaysia');
        $response->assertSee('Avg Rank');
        $response->assertSee('Rank 1s');
        $response->assertSee('Rank 2s');
        $response->assertSee('View Profile');

        // Verify Individual Voter Ballot Breakdown (Vertical Section B)
        $response->assertSee('Individual Voter Ballot Breakdown');
        $response->assertSee('Emeritus Professor Voter 1');
        $response->assertSee('voter1@asm.org.my');
        $response->assertSee('Professor Voter 2');
        $response->assertSee('voter2@asm.org.my');
        $response->assertSee('Average Rank');
        $response->assertSee('Rank 1 Votes Count');
        $response->assertSee('Rank 2 Votes Count');

        // Verify Candidate Modal is present
        $response->assertSee('candidate-modal-title');
    }

    public function test_single_candidate_discipline_shows_proper_notice_in_vertical_view(): void
    {
        $singleDiscipline = Discipline::create([
            'discipline_name' => 'CS - Chemical Sciences',
            'description' => 'Chemical Sciences',
            'display_order' => 2,
            'active' => true,
        ]);

        $singleCandidate = Candidate::create([
            'discipline_id' => $singleDiscipline->id,
            'candidate_name' => 'Professor ChM Dr Juan Joon Ching',
            'candidate_title' => 'Professor ChM Dr',
            'organisation' => 'Universiti Malaya',
            'sub_discipline' => 'Catalysis & Physical Chemistry',
            'display_order' => 1,
            'active' => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.ranking-results.index', [
            'discipline_id' => $singleDiscipline->id,
            'exercise_id' => $this->exercise->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Ranking: Not Required (Single Candidate)');
        $response->assertSee('Sole Nominee Confirmed');
        $response->assertSee('Professor ChM Dr Juan Joon Ching');
    }
}
