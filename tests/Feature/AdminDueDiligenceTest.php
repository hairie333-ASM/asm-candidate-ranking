<?php

namespace Tests\Feature;

use App\Models\Candidate;
use App\Models\Discipline;
use App\Models\DueDiligenceCategory;
use App\Models\DueDiligenceSubmission;
use App\Models\SupportingDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminDueDiligenceTest extends TestCase
{
    use RefreshDatabase;

    protected Discipline $discipline;

    protected Candidate $candidate;

    protected DueDiligenceCategory $category;

    protected User $voter;

    protected User $admin;

    protected DueDiligenceSubmission $submission;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->discipline = Discipline::create([
            'discipline_name' => 'BAES - Biological Agriculture and Environmental Sciences',
            'description' => 'Test Discipline',
            'display_order' => 1,
            'active' => true,
        ]);

        $this->candidate = Candidate::create([
            'discipline_id' => $this->discipline->id,
            'candidate_name' => 'Prof. Dr. Candidate One',
            'candidate_title' => 'Professor',
            'organisation' => 'Universiti Sains Malaysia',
            'display_order' => 1,
            'active' => true,
        ]);

        $this->category = DueDiligenceCategory::create([
            'name' => 'Scientific Integrity',
            'description' => 'Research integrity observations',
            'display_order' => 1,
            'active' => true,
        ]);

        $this->voter = User::create([
            'name' => 'Dr. Submitter Voter',
            'email' => 'voter@example.test',
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
            'active' => true,
        ]);

        $this->submission = DueDiligenceSubmission::create([
            'user_id' => $this->voter->id,
            'candidate_id' => $this->candidate->id,
            'category_id' => $this->category->id,
            'comment' => 'Candidate demonstrated exemplary scientific leadership and ethical compliance.',
            'reference_1_name' => 'Dato Dr. Reference One',
            'reference_1_designation' => 'Dean of Science',
            'reference_1_organisation' => 'Universiti Malaya',
            'reference_1_email' => 'ref1@example.test',
            'reference_1_contact_number' => '+60123456789',
        ]);
    }

    public function test_admin_can_view_due_diligence_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.due-diligence.index'));

        $response->assertStatus(200);
        $response->assertSee('Due Diligence Repository');
        $response->assertSee($this->candidate->candidate_name);
        $response->assertSee($this->voter->name);
        $response->assertSee('Scientific Integrity');
        $response->assertSee('Total Submissions');
    }

    public function test_admin_can_filter_due_diligence_submissions(): void
    {
        $otherDiscipline = Discipline::create([
            'discipline_name' => 'EECS - Engineering and Computer Sciences',
            'description' => 'EECS',
            'display_order' => 2,
            'active' => true,
        ]);

        $otherCandidate = Candidate::create([
            'discipline_id' => $otherDiscipline->id,
            'candidate_name' => 'Prof. Dr. Other Person',
            'display_order' => 1,
            'active' => true,
        ]);

        $otherSubmission = DueDiligenceSubmission::create([
            'user_id' => $this->voter->id,
            'candidate_id' => $otherCandidate->id,
            'category_id' => $this->category->id,
            'comment' => 'Another comment for EECS candidate.',
        ]);

        // Filter by discipline
        $response = $this->actingAs($this->admin)->get(route('admin.due-diligence.index', [
            'discipline_id' => $this->discipline->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee($this->candidate->candidate_name);
        $response->assertDontSee($otherCandidate->candidate_name);

        // Filter by search query
        $searchResponse = $this->actingAs($this->admin)->get(route('admin.due-diligence.index', [
            'q' => 'Other Person',
        ]));

        $searchResponse->assertStatus(200);
        $searchResponse->assertSee($otherCandidate->candidate_name);
        $searchResponse->assertDontSee($this->candidate->candidate_name);
    }

    public function test_admin_can_view_single_due_diligence_submission(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.due-diligence.show', $this->submission));

        $response->assertStatus(200);
        $response->assertSee('Due Diligence Review');
        $response->assertSee('#'.$this->submission->id);
        $response->assertSee($this->candidate->candidate_name);
        $response->assertSee($this->voter->name);
        $response->assertSee('Dato Dr. Reference One');
        $response->assertSee('ref1@example.test');
        $response->assertSee('Candidate demonstrated exemplary scientific leadership');
    }

    public function test_admin_can_preview_and_download_supporting_document(): void
    {
        $filePath = 'private/due_diligence/'.$this->submission->id.'/test-doc.pdf';
        Storage::disk('local')->put($filePath, '%PDF-1.4 Fake PDF Content');

        $document = SupportingDocument::create([
            'due_diligence_submission_id' => $this->submission->id,
            'original_filename' => 'report_ethics.pdf',
            'stored_filename' => 'test-doc.pdf',
            'storage_path' => $filePath,
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
        ]);

        // Test preview endpoint
        $previewResponse = $this->actingAs($this->admin)->get(route('admin.due-diligence.document.preview', $document));
        $previewResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $previewResponse->headers->get('Content-Type'));
        $this->assertStringContainsString('inline', $previewResponse->headers->get('Content-Disposition'));

        // Test download endpoint
        $downloadResponse = $this->actingAs($this->admin)->get(route('admin.due-diligence.document.download', $document));
        $downloadResponse->assertStatus(200);
        $this->assertStringContainsString('attachment', $downloadResponse->headers->get('Content-Disposition'));
        $this->assertStringContainsString('report_ethics.pdf', $downloadResponse->headers->get('Content-Disposition'));

        // Document is visible in show view
        $showResponse = $this->actingAs($this->admin)->get(route('admin.due-diligence.show', $this->submission));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('report_ethics.pdf');
    }

    public function test_admin_can_download_all_supporting_documents_zip(): void
    {
        $file1 = 'private/due_diligence/'.$this->submission->id.'/file1.pdf';
        $file2 = 'private/due_diligence/'.$this->submission->id.'/file2.png';
        Storage::disk('local')->put($file1, '%PDF-1.4 Content 1');
        Storage::disk('local')->put($file2, 'PNG Content 2');

        SupportingDocument::create([
            'due_diligence_submission_id' => $this->submission->id,
            'original_filename' => 'doc1.pdf',
            'stored_filename' => 'file1.pdf',
            'storage_path' => $file1,
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
        ]);

        SupportingDocument::create([
            'due_diligence_submission_id' => $this->submission->id,
            'original_filename' => 'doc2.png',
            'stored_filename' => 'file2.png',
            'storage_path' => $file2,
            'mime_type' => 'image/png',
            'file_size' => 2048,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.due-diligence.download-zip', $this->submission));

        $response->assertStatus(200);
        $this->assertEquals('application/zip', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('.zip', $response->headers->get('Content-Disposition'));
    }

    public function test_admin_handles_missing_file_gracefully(): void
    {
        $document = SupportingDocument::create([
            'due_diligence_submission_id' => $this->submission->id,
            'original_filename' => 'missing.pdf',
            'stored_filename' => 'missing.pdf',
            'storage_path' => 'private/due_diligence/non-existent.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.due-diligence.document.download', $document));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_admin_can_delete_due_diligence_submission_and_files(): void
    {
        $filePath = 'private/due_diligence/'.$this->submission->id.'/test-del.pdf';
        Storage::disk('local')->put($filePath, 'Delete Me');

        $document = SupportingDocument::create([
            'due_diligence_submission_id' => $this->submission->id,
            'original_filename' => 'test-del.pdf',
            'stored_filename' => 'test-del.pdf',
            'storage_path' => $filePath,
            'mime_type' => 'application/pdf',
            'file_size' => 500,
        ]);

        $submissionId = $this->submission->id;

        $response = $this->actingAs($this->admin)->delete(route('admin.due-diligence.destroy', $this->submission));

        $response->assertRedirect(route('admin.due-diligence.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('due_diligence_submissions', ['id' => $submissionId]);
        $this->assertDatabaseMissing('supporting_documents', ['id' => $document->id]);
        $this->assertFalse(Storage::disk('local')->exists($filePath));

        // Verify audit log
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'Due Diligence Deleted',
            'record_type' => 'DueDiligenceSubmission',
            'record_id' => $submissionId,
        ]);
    }

    public function test_non_admin_cannot_access_admin_due_diligence_pages(): void
    {
        $indexResponse = $this->actingAs($this->voter)->get(route('admin.due-diligence.index'));
        $indexResponse->assertStatus(403);

        $showResponse = $this->actingAs($this->voter)->get(route('admin.due-diligence.show', $this->submission));
        $showResponse->assertStatus(403);

        $deleteResponse = $this->actingAs($this->voter)->delete(route('admin.due-diligence.destroy', $this->submission));
        $deleteResponse->assertStatus(403);

        $zipResponse = $this->actingAs($this->voter)->get(route('admin.due-diligence.download-zip', $this->submission));
        $zipResponse->assertStatus(403);
    }
}
