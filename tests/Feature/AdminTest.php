<?php

namespace Tests\Feature;

use App\Models\Discipline;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $voter;

    protected User $reviewer;

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
            'name' => 'ASM Admin',
            'email' => 'admin@example.test',
            'password' => bcrypt('password'),
            'role' => 'administrator',
            'discipline_id' => null,
            'active' => true,
        ]);

        $this->voter = User::create([
            'name' => 'Voter One',
            'email' => 'voter1@example.test',
            'password' => bcrypt('password'),
            'role' => 'voting_user',
            'discipline_id' => $this->discipline->id,
            'active' => true,
        ]);

        $this->reviewer = User::create([
            'name' => 'Reviewer One',
            'email' => 'reviewer@example.test',
            'password' => bcrypt('password'),
            'role' => 'reviewer',
            'discipline_id' => null,
            'active' => true,
        ]);
    }

    /**
     * Test admin navigating to /dashboard redirects to /admin without TypeError.
     */
    public function test_admin_accessing_dashboard_redirects_to_admin_suite(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertRedirect(route('admin.dashboard'));

        $followResponse = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $followResponse->assertStatus(200);
        $followResponse->assertSee('ASM Management Suite');
    }

    /**
     * Test non-admin cannot access admin suite (403 Forbidden).
     */
    public function test_voter_cannot_access_admin_suite(): void
    {
        $response = $this->actingAs($this->voter)->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }

    /**
     * Test reviewer accessing dashboard renders reviewer portal.
     */
    public function test_reviewer_dashboard_renders(): void
    {
        $response = $this->actingAs($this->reviewer)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Reviewer Portal');
    }
}
