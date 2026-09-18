<?php

namespace Tests\Feature;

use App\Models\Candidate;
use App\Models\Discipline;
use App\Models\RankingExercise;
use App\Models\RankingSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingValidationTest extends TestCase
{
    use RefreshDatabase;

    protected Discipline $discipline;

    protected User $voter;

    protected User $admin;

    protected RankingExercise $exercise;

    protected Candidate $candidate1;

    protected Candidate $candidate2;

    protected Candidate $candidate3;

    protected function setUp(): void
    {
        parent::setUp();

        $this->discipline = Discipline::create([
            'discipline_name' => 'ITCS - Information Technology and Computer Sciences',
            'description' => 'IT Discipline',
            'display_order' => 4,
            'active' => true,
        ]);

        $this->exercise = RankingExercise::create([
            'exercise_name' => 'Fellowship 2026 Ranking',
            'description' => 'Ranking exercise description',
            'status' => 'Open',
            'start_datetime' => now()->subDay(),
            'end_datetime' => now()->addDays(5),
            'allow_resubmission' => false,
        ]);

        $this->voter = User::create([
            'name' => 'IT Voter',
            'email' => 'itvoter@example.test',
            'password' => bcrypt('password'),
            'role' => 'voting_user',
            'discipline_id' => $this->discipline->id,
            'active' => true,
        ]);

        $this->admin = User::create([
            'name' => 'System Admin',
            'email' => 'admin@example.test',
            'password' => bcrypt('password'),
            'role' => 'administrator',
            'discipline_id' => null,
            'active' => true,
        ]);

        $this->candidate1 = Candidate::create([
            'discipline_id' => $this->discipline->id,
            'candidate_name' => 'Prof. Alice',
            'organisation' => 'UTM',
            'display_order' => 1,
            'active' => true,
        ]);

        $this->candidate2 = Candidate::create([
            'discipline_id' => $this->discipline->id,
            'candidate_name' => 'Prof. Bob',
            'organisation' => 'USM',
            'display_order' => 2,
            'active' => true,
        ]);

        $this->candidate3 = Candidate::create([
            'discipline_id' => $this->discipline->id,
            'candidate_name' => 'Prof. Charlie',
            'organisation' => 'UM',
            'display_order' => 3,
            'active' => true,
        ]);
    }

    /**
     * Test 1: Complete and valid 1..N ranking sequence succeeds.
     */
    public function test_valid_ranking_sequence_submits_successfully(): void
    {
        $response = $this->actingAs($this->voter)->post(route('ranking.submit'), [
            'rankings' => [
                $this->candidate1->id => 1,
                $this->candidate2->id => 2,
                $this->candidate3->id => 3,
            ],
        ]);

        $response->assertRedirect(route('my-submission'));
        $this->assertDatabaseHas('ranking_submissions', [
            'user_id' => $this->voter->id,
            'discipline_id' => $this->discipline->id,
            'exercise_id' => $this->exercise->id,
            'status' => 'SUBMITTED',
        ]);
        $this->assertDatabaseHas('rankings', [
            'candidate_id' => $this->candidate1->id,
            'ranking_number' => 1,
        ]);
        $this->assertDatabaseHas('rankings', [
            'candidate_id' => $this->candidate2->id,
            'ranking_number' => 2,
        ]);
        $this->assertDatabaseHas('rankings', [
            'candidate_id' => $this->candidate3->id,
            'ranking_number' => 3,
        ]);
    }

    /**
     * Test 2: Duplicate rank detection rejects submission.
     */
    public function test_duplicate_ranking_is_rejected(): void
    {
        // Both candidate1 and candidate2 given rank 1
        $response = $this->actingAs($this->voter)->post(route('ranking.submit'), [
            'rankings' => [
                $this->candidate1->id => 1,
                $this->candidate2->id => 1,
                $this->candidate3->id => 2,
            ],
        ]);

        $response->assertSessionHasErrors(['rankings']);
        $this->assertDatabaseMissing('ranking_submissions', [
            'user_id' => $this->voter->id,
        ]);
    }

    /**
     * Test 3: Missing rank / non-contiguous sequence rejects submission.
     */
    public function test_missing_rank_sequence_is_rejected(): void
    {
        // Sequence has 1 and 3, but 2 is missing
        $response = $this->actingAs($this->voter)->post(route('ranking.submit'), [
            'rankings' => [
                $this->candidate1->id => 1,
                $this->candidate2->id => 3,
                $this->candidate3->id => 4,
            ],
        ]);

        $response->assertSessionHasErrors(['rankings']);
        $this->assertDatabaseMissing('ranking_submissions', [
            'user_id' => $this->voter->id,
        ]);
    }

    /**
     * Test 4: Incomplete ranking (omitting a candidate) rejects submission.
     */
    public function test_incomplete_candidate_ranking_is_rejected(): void
    {
        // Only ranking 2 out of 3 candidates
        $response = $this->actingAs($this->voter)->post(route('ranking.submit'), [
            'rankings' => [
                $this->candidate1->id => 1,
                $this->candidate2->id => 2,
            ],
        ]);

        $response->assertSessionHasErrors(['rankings']);
        $this->assertDatabaseMissing('ranking_submissions', [
            'user_id' => $this->voter->id,
        ]);
    }

    /**
     * Test 5: Closed exercise rejects submissions.
     */
    public function test_closed_exercise_rejects_ranking(): void
    {
        $this->exercise->update(['status' => 'Closed']);

        $response = $this->actingAs($this->voter)->post(route('ranking.submit'), [
            'rankings' => [
                $this->candidate1->id => 1,
                $this->candidate2->id => 2,
                $this->candidate3->id => 3,
            ],
        ]);

        $response->assertSessionHasErrors(['exercise']);
        $this->assertDatabaseMissing('ranking_submissions', [
            'user_id' => $this->voter->id,
        ]);
    }

    /**
     * Test 6: Locked submission cannot be resubmitted without admin reopening.
     */
    public function test_submitted_ballot_cannot_be_overwritten(): void
    {
        // Initial submission
        $this->actingAs($this->voter)->post(route('ranking.submit'), [
            'rankings' => [
                $this->candidate1->id => 1,
                $this->candidate2->id => 2,
                $this->candidate3->id => 3,
            ],
        ]);

        // Second submission attempt
        $resubmit = $this->actingAs($this->voter)->post(route('ranking.submit'), [
            'rankings' => [
                $this->candidate1->id => 3,
                $this->candidate2->id => 2,
                $this->candidate3->id => 1,
            ],
        ]);

        $resubmit->assertSessionHasErrors(['submission']);
        // Assert initial ranking 1 is still candidate1
        $this->assertDatabaseHas('rankings', [
            'candidate_id' => $this->candidate1->id,
            'ranking_number' => 1,
        ]);
    }

    /**
     * Test 7: Admin reopening ballot resets to DRAFT and allows voter to resubmit.
     */
    public function test_admin_can_reopen_submission_and_voter_can_resubmit(): void
    {
        // 1. Initial submission
        $this->actingAs($this->voter)->post(route('ranking.submit'), [
            'rankings' => [
                $this->candidate1->id => 1,
                $this->candidate2->id => 2,
                $this->candidate3->id => 3,
            ],
        ]);

        $submission = RankingSubmission::where('user_id', $this->voter->id)->firstOrFail();
        $this->assertEquals('SUBMITTED', $submission->status);

        // 2. Admin reopens the ballot with justification
        $reopenResponse = $this->actingAs($this->admin)->post(route('admin.ranking-results.reopen', $submission), [
            'reason' => 'Voter requested correction due to typographical input error.',
        ]);

        $reopenResponse->assertRedirect();
        $submission->refresh();
        $this->assertEquals('DRAFT', $submission->status);

        // 3. Voter resubmits with new ranks (swapping 1 and 3)
        $resubmitResponse = $this->actingAs($this->voter)->post(route('ranking.submit'), [
            'rankings' => [
                $this->candidate1->id => 3,
                $this->candidate2->id => 2,
                $this->candidate3->id => 1,
            ],
        ]);

        $resubmitResponse->assertRedirect(route('my-submission'));
        $submission->refresh();
        $this->assertEquals('SUBMITTED', $submission->status);

        // Assert new ranking is stored
        $this->assertDatabaseHas('rankings', [
            'candidate_id' => $this->candidate1->id,
            'ranking_number' => 3,
        ]);
        $this->assertDatabaseHas('rankings', [
            'candidate_id' => $this->candidate3->id,
            'ranking_number' => 1,
        ]);
    }

    /**
     * Test 8: Visiting ranking when exercise is Closed renders ranking.closed without error.
     */
    public function test_closed_ranking_exercise_renders_view_without_error(): void
    {
        $this->exercise->update(['status' => 'Closed']);

        $response = $this->actingAs($this->voter)->get(route('ranking.index'));

        $response->assertStatus(200);
        $response->assertViewIs('ranking.closed');
        $response->assertSee('Ranking Exercise Not Active');
        $response->assertSee('Fellowship 2026 Ranking');
        $response->assertSee('Closed');
    }

    /**
     * Test 9: Visiting ranking when no exercise exists renders ranking.closed without error.
     */
    public function test_no_ranking_exercise_renders_closed_view_without_error(): void
    {
        RankingExercise::query()->delete();

        $response = $this->actingAs($this->voter)->get(route('ranking.index'));

        $response->assertStatus(200);
        $response->assertViewIs('ranking.closed');
        $response->assertSee('Ranking Exercise Not Active');
        $response->assertSee('There is currently no active ranking exercise');
    }
}
