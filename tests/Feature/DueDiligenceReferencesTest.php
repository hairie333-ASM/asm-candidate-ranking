<?php

namespace Tests\Feature;

use App\Models\Candidate;
use App\Models\Discipline;
use App\Models\DueDiligenceCategory;
use App\Models\DueDiligenceSubmission;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DueDiligenceReferencesTest extends TestCase
{
    use RefreshDatabase;

    protected Discipline $discipline;

    protected Candidate $candidate;

    protected DueDiligenceCategory $category;

    protected User $voter;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->discipline = Discipline::create([
            'discipline_name' => 'BAES - Biological Agriculture and Environmental Sciences',
            'description' => 'BAES',
            'display_order' => 1,
            'active' => true,
        ]);

        $this->candidate = Candidate::create([
            'discipline_id' => $this->discipline->id,
            'candidate_name' => 'Dr. Siti Nurhaliza',
            'candidate_title' => 'Dr.',
            'organisation' => 'Universiti Malaya',
            'display_order' => 1,
            'active' => true,
        ]);

        $this->category = DueDiligenceCategory::create([
            'name' => 'Scientific / Academic Integrity',
            'description' => 'Observations on scientific rigor',
            'display_order' => 1,
            'active' => true,
        ]);

        $this->voter = User::create([
            'name' => 'Voter One',
            'email' => 'voter1@akademisains.gov.my',
            'username' => 'voter1',
            'password' => bcrypt('Password123!'),
            'role' => 'voting_user',
            'discipline_id' => $this->discipline->id,
            'active' => true,
        ]);

        $this->admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@akademisains.gov.my',
            'username' => 'admin',
            'password' => bcrypt('Password123!'),
            'role' => 'administrator',
            'active' => true,
        ]);
    }

    public function test_due_diligence_form_renders_reference_sections(): void
    {
        $response = $this->actingAs($this->voter)->get(route('due-diligence.create', $this->candidate));

        $response->assertStatus(200);
        $response->assertSee('Professional References');
        $response->assertSee('Reference 1');
        $response->assertSee('Mandatory');
        $response->assertSee('reference_1_name');
        $response->assertSee('reference_1_designation');
        $response->assertSee('reference_1_organisation');
        $response->assertSee('reference_1_contact_number');
        $response->assertSee('reference_1_email');

        $response->assertSee('Reference 2');
        $response->assertSee('Optional');
        $response->assertSee('reference_2_name');
    }

    public function test_due_diligence_fails_if_reference_1_is_missing(): void
    {
        $response = $this->actingAs($this->voter)->post(route('due-diligence.store'), [
            'candidate_id' => $this->candidate->id,
            'category_id' => $this->category->id,
            'comment' => 'Valid observation comment here with sufficient length.',
        ]);

        $response->assertSessionHasErrors([
            'reference_1_name',
            'reference_1_designation',
            'reference_1_organisation',
            'reference_1_contact_number',
            'reference_1_email',
        ]);
    }

    public function test_due_diligence_fails_if_reference_1_email_is_invalid(): void
    {
        $response = $this->actingAs($this->voter)->post(route('due-diligence.store'), [
            'candidate_id' => $this->candidate->id,
            'category_id' => $this->category->id,
            'comment' => 'Valid observation comment with sufficient length.',
            'reference_1_name' => 'Prof. Ahmad',
            'reference_1_designation' => 'Dean',
            'reference_1_organisation' => 'UKM',
            'reference_1_contact_number' => '+60123456789',
            'reference_1_email' => 'invalid-email-format',
        ]);

        $response->assertSessionHasErrors(['reference_1_email']);
    }

    public function test_due_diligence_fails_if_reference_2_email_is_invalid(): void
    {
        $response = $this->actingAs($this->voter)->post(route('due-diligence.store'), [
            'candidate_id' => $this->candidate->id,
            'category_id' => $this->category->id,
            'comment' => 'Valid observation comment with sufficient length.',
            'reference_1_name' => 'Prof. Ahmad',
            'reference_1_designation' => 'Dean',
            'reference_1_organisation' => 'UKM',
            'reference_1_contact_number' => '+60123456789',
            'reference_1_email' => 'ahmad@ukm.edu.my',
            'reference_2_name' => 'Dr. Lim',
            'reference_2_email' => 'not-an-email',
        ]);

        $response->assertSessionHasErrors(['reference_2_email']);
    }

    public function test_due_diligence_succeeds_with_reference_1_only(): void
    {
        $response = $this->actingAs($this->voter)->post(route('due-diligence.store'), [
            'candidate_id' => $this->candidate->id,
            'category_id' => $this->category->id,
            'comment' => 'Extremely thorough scientific record with high impact.',
            'reference_1_name' => 'Prof. Dr. Ahmad Razali',
            'reference_1_designation' => 'Dean of Science',
            'reference_1_organisation' => 'Universiti Malaya',
            'reference_1_contact_number' => '+6012-3456789',
            'reference_1_email' => 'ahmad.razali@um.edu.my',
        ]);

        $response->assertRedirect(route('due-diligence.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('due_diligence_submissions', [
            'candidate_id' => $this->candidate->id,
            'user_id' => $this->voter->id,
            'reference_1_name' => 'Prof. Dr. Ahmad Razali',
            'reference_1_email' => 'ahmad.razali@um.edu.my',
            'reference_2_name' => null,
        ]);
    }

    public function test_due_diligence_succeeds_with_both_references(): void
    {
        $response = $this->actingAs($this->voter)->post(route('due-diligence.store'), [
            'candidate_id' => $this->candidate->id,
            'category_id' => $this->category->id,
            'comment' => 'Exceptional contributions and high ethical standard.',
            'reference_1_name' => 'Prof. Dr. Ahmad Razali',
            'reference_1_designation' => 'Dean of Science',
            'reference_1_organisation' => 'Universiti Malaya',
            'reference_1_contact_number' => '+6012-3456789',
            'reference_1_email' => 'ahmad.razali@um.edu.my',
            'reference_2_name' => 'Dr. Tan Sri Johan',
            'reference_2_designation' => 'Director General',
            'reference_2_organisation' => 'SIRIM Berhad',
            'reference_2_contact_number' => '+6019-9876543',
            'reference_2_email' => 'johan@sirim.my',
        ]);

        $response->assertRedirect(route('due-diligence.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('due_diligence_submissions', [
            'candidate_id' => $this->candidate->id,
            'user_id' => $this->voter->id,
            'reference_1_name' => 'Prof. Dr. Ahmad Razali',
            'reference_2_name' => 'Dr. Tan Sri Johan',
            'reference_2_organisation' => 'SIRIM Berhad',
        ]);
    }

    public function test_admin_and_voter_views_render_reference_details(): void
    {
        $submission = DueDiligenceSubmission::create([
            'user_id' => $this->voter->id,
            'candidate_id' => $this->candidate->id,
            'category_id' => $this->category->id,
            'comment' => 'Due diligence observation comment.',
            'user_discipline_id' => $this->discipline->id,
            'reference_1_name' => 'Prof. Dr. Ahmad Razali',
            'reference_1_designation' => 'Dean',
            'reference_1_organisation' => 'UM',
            'reference_1_contact_number' => '0123456789',
            'reference_1_email' => 'ahmad@um.edu.my',
            'reference_2_name' => 'Dr. Johan',
            'reference_2_designation' => 'Director',
            'reference_2_organisation' => 'SIRIM',
            'reference_2_contact_number' => '0198765432',
            'reference_2_email' => 'johan@sirim.my',
        ]);

        // Admin Review View
        $adminView = $this->actingAs($this->admin)->get(route('admin.due-diligence.index'));
        $adminView->assertStatus(200);
        $adminView->assertSee('Prof. Dr. Ahmad Razali');
        $adminView->assertSee('ahmad@um.edu.my');
        $adminView->assertSee('Dr. Johan');

        // Voter's Own Past Submissions on Due Diligence Hub
        $voterView = $this->actingAs($this->voter)->get(route('due-diligence.index'));
        $voterView->assertStatus(200);
        $voterView->assertSee('Prof. Dr. Ahmad Razali');
        $voterView->assertSee('Dr. Johan');
    }

    public function test_due_diligence_csv_export_includes_reference_data(): void
    {
        DueDiligenceSubmission::create([
            'user_id' => $this->voter->id,
            'candidate_id' => $this->candidate->id,
            'category_id' => $this->category->id,
            'comment' => 'CSV observation.',
            'user_discipline_id' => $this->discipline->id,
            'reference_1_name' => 'Prof. Dr. Ahmad Razali',
            'reference_1_designation' => 'Dean',
            'reference_1_organisation' => 'UM',
            'reference_1_contact_number' => '0123456789',
            'reference_1_email' => 'ahmad@um.edu.my',
        ]);

        /** @var ReportService $reportService */
        $reportService = app(ReportService::class);
        $csv = $reportService->generateDueDiligenceCsv();

        $this->assertStringContainsString('Reference 1 Name', $csv);
        $this->assertStringContainsString('Reference 1 Email', $csv);
        $this->assertStringContainsString('Reference 2 Name', $csv);
        $this->assertStringContainsString('Prof. Dr. Ahmad Razali', $csv);
        $this->assertStringContainsString('ahmad@um.edu.my', $csv);
    }

    public function test_due_diligence_ui_strictly_uses_due_diligence_without_peer_feedback(): void
    {
        $indexResponse = $this->actingAs($this->voter)->get(route('due-diligence.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Due Diligence');
        $indexResponse->assertDontSee('Due Diligence & Peer Feedback');
        $indexResponse->assertDontSee('Peer Feedback');
        $indexResponse->assertSee('Submit Due Diligence');
        $indexResponse->assertDontSee('Submit Feedback');

        $createResponse = $this->actingAs($this->voter)->get(route('due-diligence.create', $this->candidate));
        $createResponse->assertStatus(200);
        $createResponse->assertSee('Due Diligence Category');
        $createResponse->assertDontSee('Feedback Category');
    }
}
