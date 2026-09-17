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

class AdminUserDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $voter;

    protected Discipline $discipline;

    protected function setUp(): void
    {
        parent::setUp();

        $this->discipline = Discipline::create([
            'discipline_name' => 'BAES - Biological Agriculture and Environmental Sciences',
            'description' => 'Test Discipline',
            'display_order' => 1,
            'active' => true,
        ]);

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.test',
            'password' => bcrypt('password'),
            'role' => 'administrator',
            'discipline_id' => null,
            'active' => true,
        ]);

        $this->voter = User::create([
            'name' => 'Voter Candidate',
            'email' => 'voter@example.test',
            'password' => bcrypt('password'),
            'role' => 'voting_user',
            'discipline_id' => $this->discipline->id,
            'active' => true,
        ]);
    }

    /**
     * Test admin can delete another user.
     */
    public function test_admin_can_delete_user(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->voter));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('users', [
            'id' => $this->voter->id,
            'email' => 'voter@example.test',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'User Deleted',
            'record_type' => 'User',
            'record_id' => $this->voter->id,
        ]);
    }

    /**
     * Test admin cannot delete their own account.
     */
    public function test_admin_cannot_delete_self(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->admin));

        $response->assertSessionHas('error', 'You cannot delete your own account.');

        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
            'email' => 'admin@example.test',
        ]);
    }

    /**
     * Test admin cannot delete the only administrator account.
     */
    public function test_cannot_delete_only_administrator(): void
    {
        // Second admin deletes the only other admin
        $secondAdmin = User::create([
            'name' => 'Second Admin',
            'email' => 'admin2@example.test',
            'password' => bcrypt('password'),
            'role' => 'administrator',
            'discipline_id' => null,
            'active' => true,
        ]);

        // First delete second admin -> succeeds (1 admin remains)
        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $secondAdmin));
        $response->assertSessionHas('success');

        $this->assertEquals(1, User::where('role', 'administrator')->count());
    }

    /**
     * Test non-admin user cannot delete users.
     */
    public function test_non_admin_cannot_delete_users(): void
    {
        $response = $this->actingAs($this->voter)->delete(route('admin.users.destroy', $this->admin));

        $response->assertStatus(403);

        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
        ]);
    }

    /**
     * Test user deletion cleans up related records.
     */
    public function test_user_deletion_cascades_safely(): void
    {
        $exercise = RankingExercise::create([
            'exercise_name' => 'Test Exercise',
            'status' => 'open',
            'start_datetime' => now(),
            'end_datetime' => now()->addDays(7),
        ]);

        $candidate = Candidate::create([
            'discipline_id' => $this->discipline->id,
            'candidate_name' => 'Prof. Example',
            'display_order' => 1,
            'active' => true,
        ]);

        $rankingSubmission = RankingSubmission::create([
            'user_id' => $this->voter->id,
            'exercise_id' => $exercise->id,
            'discipline_id' => $this->discipline->id,
            'submitted_at' => now(),
        ]);

        $ranking = Ranking::create([
            'submission_id' => $rankingSubmission->id,
            'user_id' => $this->voter->id,
            'exercise_id' => $exercise->id,
            'discipline_id' => $this->discipline->id,
            'candidate_id' => $candidate->id,
            'ranking_number' => 1,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->voter));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $this->voter->id]);
        $this->assertDatabaseMissing('ranking_submissions', ['id' => $rankingSubmission->id]);
        $this->assertDatabaseMissing('rankings', ['id' => $ranking->id]);
    }
}
