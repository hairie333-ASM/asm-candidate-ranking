<?php

namespace Tests\Feature;

use App\Models\Discipline;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EvaluationRubricTest extends TestCase
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
            'name' => 'Evaluation Tester',
            'email' => 'evaluator@example.test',
            'password' => bcrypt('password123'),
            'role' => 'voting_user',
            'discipline_id' => $discipline->id,
            'active' => true,
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/evaluation-rubric');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_evaluation_rubric_page(): void
    {
        $response = $this->actingAs($this->user)->get('/evaluation-rubric');

        $response->assertStatus(200);
        $response->assertSee('Evaluation Rubric');
        $response->assertSee('The standard evaluation rubric were used by seven discipline groups');
        $response->assertSee('BAES - Biological Agriculture and Environmental Sciences');
        $response->assertSee('CS - Chemical Sciences');
        $response->assertSee('ES - Engineering Sciences');
        $response->assertSee('ITCS - Information Technology and Computer Sciences');
        $response->assertSee('MHS - Medical and Health Sciences');
        $response->assertSee('MPES - Mathematical and Physical Sciences');
        $response->assertSee('SSH - Social Sciences and Humanities');
        $response->assertSee('Scientific/ Technical/ Knowledge contributions');
        $response->assertSee('Leadership');
        $response->assertSee('Contributions to Nation');
        $response->assertSee('Capacity Building');
        $response->assertSee('Current/ past contributions to ASM and/or society', false);
        $response->assertSee('International or Regional Networking');
        $response->assertSee('Science & Technology Development and Industry', false);
        $response->assertSee('Membership Committee');
    }

    public function test_navigation_bar_contains_evaluation_rubric_tab(): void
    {
        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Evaluation Rubric');
        $response->assertSee(route('evaluation-rubric'));
    }
}
