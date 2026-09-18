<?php

namespace Tests\Feature;

use App\Models\Discipline;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $discipline = Discipline::create([
            'discipline_name' => 'BAES - Biological Agriculture and Environmental Sciences',
            'description' => 'Test BAES Discipline',
            'display_order' => 1,
            'active' => true,
        ]);

        $this->user = User::create([
            'name' => 'Test Voter',
            'email' => 'voter@example.test',
            'password' => bcrypt('password123'),
            'role' => 'voting_user',
            'discipline_id' => $discipline->id,
            'active' => true,
        ]);
    }

    public function test_logged_out_navigation_contains_only_home_and_login(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);

        // Verify Home and Login links are present
        $response->assertSee(route('home'));
        $response->assertSee(route('login'));

        // Verify Other Information is NOT present in the navigation
        $response->assertDontSee('Other Information');
        $response->assertDontSee(route('other-information'));
    }

    public function test_logged_out_home_page_hides_three_information_boxes(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);

        // Box 1, Box 2, Box 3 content must not be present
        $response->assertDontSee('Ordinal Ranking Methodology');
        $response->assertDontSee('Real-Time Duplicate Prevention');
        $response->assertDontSee('Due Diligence & Nomination Dossiers', false);
    }

    public function test_home_page_heading_displays_the_8_disciplines(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);

        // Updated heading
        $response->assertSee('The 8 Disciplines');
        // Old heading must not appear
        $response->assertDontSee('The 8 Shortlisted Disciplines');
    }

    public function test_logged_in_user_retains_existing_navigation_and_three_boxes(): void
    {
        $response = $this->actingAs($this->user)->get(route('home'));

        $response->assertStatus(200);

        // Navigation for logged-in user includes Other Information and My Dashboard
        $response->assertSee('Other Information');
        $response->assertSee(route('other-information'));
        $response->assertSee('My Dashboard');

        // Box 1, Box 2, Box 3 are visible to authenticated users
        $response->assertSee('Ordinal Ranking Methodology');
        $response->assertSee('Real-Time Duplicate Prevention');
        $response->assertSee('Due Diligence & Nomination Dossiers', false);

        // Heading is updated
        $response->assertSee('The 8 Disciplines');
        $response->assertDontSee('The 8 Shortlisted Disciplines');
    }
}
