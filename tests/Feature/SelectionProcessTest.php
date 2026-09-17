<?php

namespace Tests\Feature;

use App\Models\Discipline;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SelectionProcessTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $discipline = Discipline::create([
            'discipline_name' => 'BAES - Biological Agriculture and Environmental Sciences',
            'description' => 'Test Discipline',
            'display_order' => 1,
            'active' => true,
        ]);

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'user@example.test',
            'password' => bcrypt('password123'),
            'role' => 'voting_user',
            'discipline_id' => $discipline->id,
            'active' => true,
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/selection-process');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_selection_process_page(): void
    {
        $response = $this->actingAs($this->user)->get('/selection-process');

        $response->assertStatus(200);
        $response->assertSee('Selection Process');
        $response->assertSee('STEP 1: Nomination');
        $response->assertSee('STEP 2: Administrative Screening');
        $response->assertSee('STEP 3: Vetting Exercise');
        $response->assertSee('STEP 4: Review of Shortlisted Nominees');
        $response->assertSee('STEP 5: Ranking by Peers');
        $response->assertSee('STEP 6: Final Shortlisting');
        $response->assertSee('STEP 7: Recommendation to AGM');
        $response->assertSee('STEP 8: Election of New Fellow');
    }
}
