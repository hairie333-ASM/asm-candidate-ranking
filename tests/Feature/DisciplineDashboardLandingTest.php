<?php

namespace Tests\Feature;

use App\Models\Candidate;
use App\Models\Discipline;
use App\Models\RankingExercise;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisciplineDashboardLandingTest extends TestCase
{
    use RefreshDatabase;

    protected RankingExercise $exercise;

    protected function setUp(): void
    {
        parent::setUp();

        $this->exercise = RankingExercise::create([
            'exercise_name' => 'ASM Fellowship Ranking Cycle 2026',
            'description' => 'Official evaluation cycle',
            'status' => 'Open',
            'start_datetime' => now()->subDays(1),
            'end_datetime' => now()->addDays(7),
        ]);
    }

    public function test_guest_is_redirected_to_login_from_dashboard(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_itcs_user_automatically_lands_on_itcs_dossier(): void
    {
        $discipline = Discipline::create([
            'discipline_name' => 'Information Technology and Computer Sciences (ITCS)',
            'description' => 'ITCS discipline',
            'display_order' => 1,
            'active' => true,
        ]);

        $candidate1 = Candidate::create([
            'discipline_id' => $discipline->id,
            'candidate_name' => 'Professor Ir Dr Hafizal Mohamad',
            'organisation' => 'Universiti Sains Islam Malaysia',
            'active' => true,
        ]);

        $candidate2 = Candidate::create([
            'discipline_id' => $discipline->id,
            'candidate_name' => 'YM Raja Azrina Raja Othman',
            'organisation' => 'National Cyber Security Agency',
            'active' => true,
        ]);

        $voter = User::create([
            'name' => 'Dr. ITCS Voter',
            'email' => 'itcs.voter@akademisains.gov.my',
            'password' => bcrypt('password'),
            'role' => 'voting_user',
            'discipline_id' => $discipline->id,
            'active' => true,
        ]);

        $response = $this->actingAs($voter)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('ITCS Discipline Group');
        $response->assertSee('Information Technology and Computer Sciences');
        $response->assertSee('Dear Fellows of the Information Technology and Computer Sciences (ITCS) Discipline Group,');
        $response->assertSee('6 February 2026');
        $response->assertSee('Two (2) nominees were evaluated based on the evaluation rubric');
        $response->assertSee('27 February and 3 March 2026');
        $response->assertSee('Professor Ir Dr Hafizal Mohamad');
        $response->assertSee('YM Raja Azrina Raja Othman');
        $response->assertSee('1200 hours');
        $response->assertSee('Discipline Ranking Procedure');
    }

    public function test_baes_user_automatically_lands_on_baes_dossier(): void
    {
        $discipline = Discipline::create([
            'discipline_name' => 'Biological, Agricultural and Environmental Sciences (BAES)',
            'description' => 'BAES discipline',
            'display_order' => 2,
            'active' => true,
        ]);

        $voter = User::create([
            'name' => 'Dr. BAES Voter',
            'email' => 'baes.voter@akademisains.gov.my',
            'password' => bcrypt('password'),
            'role' => 'voting_user',
            'discipline_id' => $discipline->id,
            'active' => true,
        ]);

        $response = $this->actingAs($voter)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('BAES Discipline Group');
        $response->assertSee('Dear Fellows of the Biological, Agricultural and Environmental Sciences (BAES) Discipline Group,');
        $response->assertSee('5 February 2026');
        $response->assertSee('Eleven (11) nominees were evaluated');
        $response->assertSee('six (6) nominees for the BAES Discipline Group');
        $response->assertSee('Professor Dr Ahmad Ainuddin Nuruddin');
        $response->assertSee('Professor Ts Dr Chong Khim Phin');
        $response->assertSee('Professor Dr Sreeramanan Subramaniam');
        $response->assertSee('1200 hours');
    }

    public function test_mpes_user_automatically_lands_on_mpes_single_candidate_dossier(): void
    {
        $discipline = Discipline::create([
            'discipline_name' => 'Mathematics, Physics and Earth Sciences (MPES)',
            'description' => 'MPES discipline',
            'display_order' => 3,
            'active' => true,
        ]);

        $candidate = Candidate::create([
            'discipline_id' => $discipline->id,
            'candidate_name' => 'Professor Dr Yong Ken Tye',
            'organisation' => 'Nanyang Technological University',
            'active' => true,
        ]);

        $voter = User::create([
            'name' => 'Dr. MPES Voter',
            'email' => 'mpes.voter@akademisains.gov.my',
            'password' => bcrypt('password'),
            'role' => 'voting_user',
            'discipline_id' => $discipline->id,
            'active' => true,
        ]);

        $response = $this->actingAs($voter)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('MPES Discipline Group');
        $response->assertSee('Dear Fellows of the Mathematics, Physics and Earth Sciences (MPES) Discipline Group,');
        $response->assertSee('9 February 2026');
        $response->assertSee('online ranking by discipline group, is deemed unnecessary');
        $response->assertSee('Professor Dr Yong Ken Tye');
        $response->assertSee('Single Nominee Advancement Standard');
        $response->assertSee('Sole Nominee Confirmed');
        $response->assertSee('Not Required ✓');
        $response->assertSee('SATISFIED ✓');
    }

    public function test_cs_user_automatically_lands_on_cs_single_candidate_dossier(): void
    {
        $discipline = Discipline::create([
            'discipline_name' => 'Chemical Sciences (CS)',
            'description' => 'CS discipline',
            'display_order' => 4,
            'active' => true,
        ]);

        $candidate = Candidate::create([
            'discipline_id' => $discipline->id,
            'candidate_name' => 'Professor ChM Dr Juan Joon Ching',
            'organisation' => 'Universiti Malaya',
            'active' => true,
        ]);

        $voter = User::create([
            'name' => 'Dr. CS Voter',
            'email' => 'cs.voter@akademisains.gov.my',
            'password' => bcrypt('password'),
            'role' => 'voting_user',
            'discipline_id' => $discipline->id,
            'active' => true,
        ]);

        $response = $this->actingAs($voter)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('CS Discipline Group');
        $response->assertSee('Dear Fellows of the Chemical Sciences (CS) Discipline Group,');
        $response->assertSee('29 January 2026');
        $response->assertSee('online ranking by discipline group, is deemed unnecessary');
        $response->assertSee('Professor ChM Dr Juan Joon Ching');
        $response->assertSee('Single Nominee Advancement Standard');
    }

    public function test_stdi_user_automatically_lands_on_stdi_dossier_with_0900_hours(): void
    {
        $discipline = Discipline::create([
            'discipline_name' => 'Science, Technology and Development Industry (STDI)',
            'description' => 'STDI discipline',
            'display_order' => 5,
            'active' => true,
        ]);

        $voter = User::create([
            'name' => 'Dr. STDI Voter',
            'email' => 'stdi.voter@akademisains.gov.my',
            'password' => bcrypt('password'),
            'role' => 'voting_user',
            'discipline_id' => $discipline->id,
            'active' => true,
        ]);

        $response = $this->actingAs($voter)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('STDI Discipline Group');
        $response->assertSee('Science &amp; Technology Development and Industry', false);
        $response->assertSee('Dear Fellows of the Science &amp; Technology Development and Industry (STDI) Discipline Group,', false);
        $response->assertSee('0900 hours');
        $response->assertSee('Abdahir Haji Abdul Majid');
        $response->assertSee('Ismail Hashim');
    }

    public function test_es_user_automatically_lands_on_es_dossier(): void
    {
        $discipline = Discipline::create([
            'discipline_name' => 'Engineering Sciences (ES)',
            'description' => 'ES discipline',
            'display_order' => 6,
            'active' => true,
        ]);

        $voter = User::create([
            'name' => 'Dr. ES Voter',
            'email' => 'es.voter@akademisains.gov.my',
            'password' => bcrypt('password'),
            'role' => 'voting_user',
            'discipline_id' => $discipline->id,
            'active' => true,
        ]);

        $response = $this->actingAs($voter)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('ES Discipline Group');
        $response->assertSee('Dear Fellows of the Engineering Sciences (ES) Discipline Group,');
        $response->assertSee('10 and 11 February 2026');
        $response->assertSee('Twenty-two (22) nominees were evaluated');
        $response->assertSee('ten (10) nominees for the ES Discipline Group');
        $response->assertSee('Ahmad Farhan Mohd Sadullah');
    }

    public function test_mhs_user_automatically_lands_on_mhs_dossier(): void
    {
        $discipline = Discipline::create([
            'discipline_name' => 'Medical and Health Sciences (MHS)',
            'description' => 'MHS discipline',
            'display_order' => 7,
            'active' => true,
        ]);

        $voter = User::create([
            'name' => 'Dr. MHS Voter',
            'email' => 'mhs.voter@akademisains.gov.my',
            'password' => bcrypt('password'),
            'role' => 'voting_user',
            'discipline_id' => $discipline->id,
            'active' => true,
        ]);

        $response = $this->actingAs($voter)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('MHS Discipline Group');
        $response->assertSee('Dear Fellows of the Medical and Health Sciences (MHS) Discipline Group,');
        $response->assertSee('30 January 2026');
        $response->assertSee('Professor Dr Gan Shiaw Sze');
        $response->assertSee('Mohd Zaki Salleh');
    }

    public function test_ssh_user_automatically_lands_on_ssh_dossier(): void
    {
        $discipline = Discipline::create([
            'discipline_name' => 'Social Sciences and Humanities (SSH)',
            'description' => 'SSH discipline',
            'display_order' => 8,
            'active' => true,
        ]);

        $voter = User::create([
            'name' => 'Dr. SSH Voter',
            'email' => 'ssh.voter@akademisains.gov.my',
            'password' => bcrypt('password'),
            'role' => 'voting_user',
            'discipline_id' => $discipline->id,
            'active' => true,
        ]);

        $response = $this->actingAs($voter)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('SSH Discipline Group');
        $response->assertSee('Dear Fellows of the Social Sciences and Humanities (SSH) Discipline Group,');
        $response->assertSee('4 February 2026');
        $response->assertSee('Nine (9) nominees were evaluated');
        $response->assertSee('six (6) nominees for the SSH Discipline Group');
        $response->assertSee('Ms Chee Yoke Ling');
        $response->assertSee('Norzaini Azman');
    }

    public function test_admin_can_preview_discipline_landing_pages(): void
    {
        $discipline = Discipline::create([
            'discipline_name' => 'Information Technology and Computer Sciences (ITCS)',
            'description' => 'ITCS discipline',
            'display_order' => 1,
            'active' => true,
        ]);

        $admin = User::create([
            'name' => 'Admin Officer',
            'email' => 'admin@akademisains.gov.my',
            'password' => bcrypt('password'),
            'role' => 'administrator',
            'active' => true,
        ]);

        // Default admin goes to admin.dashboard
        $response = $this->actingAs($admin)->get('/dashboard');
        $response->assertRedirect(route('admin.dashboard'));

        // Admin preview parameter renders discipline landing dossier
        $previewResponse = $this->actingAs($admin)->get('/dashboard?preview_discipline='.$discipline->id);
        $previewResponse->assertStatus(200);
        $previewResponse->assertSee('ITCS Discipline Group');
        $previewResponse->assertSee('Official Communiqué &bull; ITCS', false);
    }
}
