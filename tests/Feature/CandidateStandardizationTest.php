<?php

namespace Tests\Feature;

use App\Models\Candidate;
use App\Models\Discipline;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CandidateStandardizationTest extends TestCase
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
            'email' => 'admin@akademisains.gov.my',
            'password' => bcrypt('password123'),
            'role' => 'administrator',
            'active' => true,
        ]);

        $this->voter = User::create([
            'name' => 'Voter Fellow',
            'email' => 'voter@akademisains.gov.my',
            'password' => bcrypt('password123'),
            'role' => 'voting_user',
            'discipline_id' => $this->discipline->id,
            'active' => true,
        ]);
    }

    public function test_admin_can_view_add_candidate_form_with_standardized_fields(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.candidates.create'));

        $response->assertStatus(200);
        $response->assertSee('Add Shortlisted Candidate');
        $response->assertSee('1. Picture');
        $response->assertSee('2. Full Name');
        $response->assertSee('3. Title / Designation');
        $response->assertSee('4. Nominated Discipline');
        $response->assertSee('5. Affiliation to ASM');
        $response->assertSee('6. OneDrive Dossier Link (URL)');
        $response->assertSee('7. Areas of Expertise');
        $response->assertSee('8. Qualifications / Professional Memberships');
        $response->assertSee('9. Basis of Recommendation');
        $response->assertSee('BAES - Biological Agriculture and Environmental Sciences');
    }

    public function test_candidate_creation_requires_mandatory_fields(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.candidates.store'), []);

        $response->assertSessionHasErrors([
            'candidate_name',
            'candidate_title',
            'discipline_id',
            'affiliation_to_asm',
        ]);
    }

    public function test_candidate_creation_validates_onedrive_url(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.candidates.store'), [
            'candidate_name' => 'Dr. Test Candidate',
            'candidate_title' => 'Professor of Chemistry',
            'discipline_id' => $this->discipline->id,
            'affiliation_to_asm' => 'YSN-ASM Member',
            'nomination_form_url' => 'not-a-valid-url',
        ]);

        $response->assertSessionHasErrors(['nomination_form_url']);
    }

    public function test_admin_can_successfully_create_candidate_with_9_fields(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('profile.jpg', 400, 500);

        $response = $this->actingAs($this->admin)->post(route('admin.candidates.store'), [
            'candidate_name' => 'Prof. Dr. Siti Aminah',
            'candidate_title' => 'Professor of Applied Chemistry',
            'discipline_id' => $this->discipline->id,
            'affiliation_to_asm' => 'TRP Recipient & Task Force Member',
            'nomination_form_url' => 'https://onedrive.live.com/?id=ASM_NOMINEE_DOSSIER_123',
            'photo' => $file,
            'area_of_expertise' => "Green Catalysis\nPolymer Chemistry",
            'qualifications_professional_memberships' => "PhD, Cambridge University\nFellow, Royal Society of Chemistry",
            'basis_of_recommendation' => 'Pioneering green energy catalyst patents with substantial industrial adoption.',
            'organisation' => 'Universiti Kebangsaan Malaysia',
            'active' => '1',
        ]);

        $response->assertRedirect(route('admin.candidates.index'));
        $this->assertDatabaseHas('candidates', [
            'candidate_name' => 'Prof. Dr. Siti Aminah',
            'candidate_title' => 'Professor of Applied Chemistry',
            'discipline_id' => $this->discipline->id,
            'affiliation_to_asm' => 'TRP Recipient & Task Force Member',
            'nomination_form_url' => 'https://onedrive.live.com/?id=ASM_NOMINEE_DOSSIER_123',
            'qualifications_professional_memberships' => "PhD, Cambridge University\nFellow, Royal Society of Chemistry",
        ]);
    }

    public function test_admin_can_view_edit_candidate_form_with_prepopulated_fields(): void
    {
        $candidate = Candidate::create([
            'discipline_id' => $this->discipline->id,
            'candidate_name' => 'Prof. Dr. Zainal Abidin',
            'candidate_title' => 'Distinguished Professor',
            'affiliation_to_asm' => 'ASM Task Force Member',
            'nomination_form_url' => 'https://onedrive.live.com/dossier-456',
            'area_of_expertise' => 'Artificial Intelligence in Agriculture',
            'qualifications_professional_memberships' => 'PhD in Computer Science',
            'basis_of_recommendation' => 'Groundbreaking precision agriculture implementations.',
            'display_order' => 1,
            'active' => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.candidates.edit', $candidate));

        $response->assertStatus(200);
        $response->assertSee('Edit Candidate: Prof. Dr. Zainal Abidin');
        $response->assertSee('1. Picture');
        $response->assertSee('2. Full Name');
        $response->assertSee('3. Title / Designation');
        $response->assertSee('4. Nominated Discipline');
        $response->assertSee('5. Affiliation to ASM');
        $response->assertSee('6. OneDrive Dossier Link (URL)');
        $response->assertSee('7. Areas of Expertise');
        $response->assertSee('8. Qualifications / Professional Memberships');
        $response->assertSee('9. Basis of Recommendation');
        $response->assertSee('Prof. Dr. Zainal Abidin');
        $response->assertSee('ASM Task Force Member');
    }

    public function test_candidate_card_rendered_in_directory_and_modal_api(): void
    {
        $candidate = Candidate::create([
            'discipline_id' => $this->discipline->id,
            'candidate_name' => 'Prof. Dr. Halim Hashim',
            'candidate_title' => 'Professor of Renewable Energy',
            'affiliation_to_asm' => 'Young Scientist Network (YSN-ASM)',
            'nomination_form_url' => 'https://onedrive.live.com/dossier-789',
            'area_of_expertise' => 'Biofuel synthesis',
            'qualifications_professional_memberships' => 'PhD in Chemical Engineering',
            'basis_of_recommendation' => 'High impact publications and national biofuel policy advisor.',
            'display_order' => 1,
            'active' => true,
        ]);

        // Candidate search directory renders standard card
        $browseResponse = $this->actingAs($this->voter)->get(route('candidates.index'));
        $browseResponse->assertStatus(200);
        $browseResponse->assertSee('Prof. Dr. Halim Hashim');
        $browseResponse->assertSee('Young Scientist Network (YSN-ASM)');
        $browseResponse->assertSee('View OneDrive Dossier');
        $browseResponse->assertSee('Affiliation to ASM');
        $browseResponse->assertSee('Areas of Expertise');
        $browseResponse->assertSee('Qualifications / Professional Memberships');
        $browseResponse->assertSee('Basis of Recommendation');

        // Modal API returns standardized attributes
        $apiResponse = $this->actingAs($this->voter)->get(route('api.candidates.show', $candidate));
        $apiResponse->assertStatus(200);
        $apiResponse->assertJsonFragment([
            'full_name' => 'Prof. Dr. Halim Hashim',
            'title_designation' => 'Professor of Renewable Energy',
            'affiliation_to_asm' => 'Young Scientist Network (YSN-ASM)',
            'onedrive_dossier_link' => 'https://onedrive.live.com/dossier-789',
            'qualifications_professional_memberships' => 'PhD in Chemical Engineering',
        ]);
    }

    /**
     * Verify modal API endpoint is publicly accessible so reverse proxy / cross-origin / Safari cookies do not cause 401.
     */
    public function test_candidate_modal_api_is_accessible_without_session_auth_to_prevent_render_modal_errors(): void
    {
        $candidate = Candidate::create([
            'discipline_id' => $this->discipline->id,
            'candidate_name' => 'Dr. Test Candidate',
            'candidate_title' => 'Associate Professor',
            'organisation' => 'Universiti Sains Malaysia',
            'display_order' => 1,
            'active' => true,
        ]);

        // Unauthenticated guest request
        $response = $this->get(route('api.candidates.show', $candidate));
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $candidate->id,
            'candidate_name' => 'Dr. Test Candidate',
            'organisation' => 'Universiti Sains Malaysia',
        ]);
    }

    /**
     * Verify candidate photos are accessible via both /storage/photos/ and /photos/ routes.
     */
    public function test_candidate_photos_are_accessible_via_both_storage_and_direct_routes(): void
    {
        // 1. Storage photo route
        $storageResponse = $this->get('/storage/photos/ainuddin-nuruddin.png');
        $storageResponse->assertStatus(200);

        // 2. Direct photos route
        $directResponse = $this->get('/photos/ainuddin-nuruddin.png');
        $directResponse->assertStatus(200);

        // 3. Non-existent photo returns 404
        $notFoundResponse = $this->get('/storage/photos/non-existent-candidate-photo-999.png');
        $notFoundResponse->assertStatus(404);
    }
}
