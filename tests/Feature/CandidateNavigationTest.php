<?php

namespace Tests\Feature;

use App\Models\Discipline;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CandidateNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected User $voter;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $discipline = Discipline::create([
            'discipline_name' => 'BAES - Biological Agriculture and Environmental Sciences',
            'description' => 'Test Discipline',
            'display_order' => 1,
            'active' => true,
        ]);

        $this->voter = User::create([
            'name' => 'Voter One',
            'email' => 'voter1@example.test',
            'password' => bcrypt('password123'),
            'role' => 'voting_user',
            'discipline_id' => $discipline->id,
            'active' => true,
        ]);

        $this->admin = User::create([
            'name' => 'System Admin',
            'email' => 'admin@example.test',
            'password' => bcrypt('password123'),
            'role' => 'administrator',
            'active' => true,
        ]);
    }

    public function test_system_name_rendered_on_login_and_home(): void
    {
        $homeResponse = $this->get('/');
        $homeResponse->assertStatus(200);
        $homeResponse->assertSee('Selection Exercise for New Election Fellow');

        $loginResponse = $this->get('/login');
        $loginResponse->assertStatus(200);
        $loginResponse->assertSee('Selection Exercise for New Election Fellow');
    }

    public function test_candidate_search_is_hidden_from_voting_user_nav_and_dashboard(): void
    {
        $response = $this->actingAs($this->voter)->get('/dashboard');
        $response->assertStatus(200);

        // Regular voter should NOT see "Candidate Search (All 8 Disciplines)" in navigation
        $response->assertDontSee('Candidate Search (All 8 Disciplines)');
        $response->assertDontSee('Search All Candidates (All 8 Disciplines)');

        // System brand name is visible
        $response->assertSee('Selection Exercise for New Election Fellow');
    }

    public function test_admin_can_see_candidate_search_link(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin');
        $response->assertStatus(200);

        // Admin can see candidate search
        $response->assertSee('Candidate Search (All 8 Disciplines)');
    }
}
