<?php

namespace Tests\Feature;

use App\Models\Discipline;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthLoginTest extends TestCase
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
            'name' => 'Voter One',
            'username' => 'voter1.login',
            'email' => 'voter1@akademisains.gov.my',
            'password' => bcrypt('password123'),
            'role' => 'voting_user',
            'discipline_id' => $discipline->id,
            'active' => true,
        ]);
    }

    public function test_login_page_renders_confidentiality_checkbox(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('I agree to maintain the confidentiality of the contents made available to me in this exercise.');
        $response->assertSee('name="confidentiality_agreement"', false);
    }

    public function test_login_fails_if_confidentiality_checkbox_not_ticked(): void
    {
        $response = $this->post('/login', [
            'email' => 'voter1@akademisains.gov.my',
            'password' => 'password123',
            // confidentiality_agreement omitted
        ]);

        $response->assertSessionHasErrors('confidentiality_agreement');
        $this->assertGuest();
    }

    public function test_login_succeeds_with_email_and_confidentiality_ticked(): void
    {
        $response = $this->post('/login', [
            'email' => 'voter1@akademisains.gov.my',
            'password' => 'password123',
            'confidentiality_agreement' => '1',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->user);
    }

    public function test_login_succeeds_with_username(): void
    {
        $response = $this->post('/login', [
            'email' => 'voter1.login',
            'password' => 'password123',
            'confidentiality_agreement' => '1',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->user);
    }

    public function test_user_can_logout_via_post(): void
    {
        $response = $this->actingAs($this->user)->post('/logout');

        $response->assertRedirect('/login');
        $response->assertSessionHas('status', 'You have been logged out securely.');
        $this->assertGuest();
    }

    public function test_user_can_logout_via_get(): void
    {
        $response = $this->actingAs($this->user)->get('/logout');

        $response->assertRedirect('/login');
        $response->assertSessionHas('status', 'You have been logged out securely.');
        $this->assertGuest();
    }

    public function test_guest_accessing_logout_redirects_to_login(): void
    {
        $response = $this->get('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
