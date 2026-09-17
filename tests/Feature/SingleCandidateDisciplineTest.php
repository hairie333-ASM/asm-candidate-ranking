<?php

namespace Tests\Feature;

use App\Models\Candidate;
use App\Models\Discipline;
use App\Models\RankingExercise;
use App\Models\User;
use App\Services\RankingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SingleCandidateDisciplineTest extends TestCase
{
    use RefreshDatabase;

    protected Discipline $disciplineSingle;

    protected Discipline $disciplineMulti;

    protected User $voterSingle;

    protected User $voterMulti;

    protected Candidate $candidate1;

    protected Candidate $candidate2;

    protected Candidate $candidate3;

    protected RankingExercise $exercise;

    protected RankingService $rankingService;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create single-candidate discipline (CS)
        $this->disciplineSingle = Discipline::create([
            'discipline_name' => 'CS - Chemical Sciences',
            'description' => 'Chemical Sciences discipline',
            'display_order' => 2,
            'active' => true,
        ]);

        // 2. Create multi-candidate discipline (BAES)
        $this->disciplineMulti = Discipline::create([
            'discipline_name' => 'BAES - Biological Agriculture and Environmental Sciences',
            'description' => 'Biological Sciences discipline',
            'display_order' => 1,
            'active' => true,
        ]);

        // 3. Create active ranking exercise
        $this->exercise = RankingExercise::create([
            'exercise_name' => 'ASM Fellowship Ranking Cycle 2026',
            'description' => 'Official evaluation cycle',
            'status' => 'Open',
            'start_datetime' => now()->subDays(1),
            'end_datetime' => now()->addDays(7),
            'allow_resubmission' => false,
        ]);

        // 4. Create single candidate in CS
        $this->candidate1 = Candidate::create([
            'discipline_id' => $this->disciplineSingle->id,
            'candidate_name' => 'Professor ChM Dr Juan Joon Ching',
            'candidate_title' => 'Professor of Nanotechnology',
            'organisation' => 'Universiti Malaya',
            'area_of_expertise' => 'Nanotechnology & Solar Energy',
            'active' => true,
            'display_order' => 1,
        ]);

        // 5. Create two candidates in BAES
        $this->candidate2 = Candidate::create([
            'discipline_id' => $this->disciplineMulti->id,
            'candidate_name' => 'Professor Dr Alice Smith',
            'candidate_title' => 'Professor of Biotechnology',
            'organisation' => 'Universiti Putra Malaysia',
            'active' => true,
            'display_order' => 1,
        ]);

        $this->candidate3 = Candidate::create([
            'discipline_id' => $this->disciplineMulti->id,
            'candidate_name' => 'Professor Dr Bob Jones',
            'candidate_title' => 'Professor of Environmental Science',
            'organisation' => 'Universiti Kebangsaan Malaysia',
            'active' => true,
            'display_order' => 2,
        ]);

        // 6. Create voting users
        $this->voterSingle = User::create([
            'name' => 'Dr Single Voter',
            'email' => 'voter.single@akademisains.gov.my',
            'password' => bcrypt('Password123!'),
            'role' => 'voting_user',
            'discipline_id' => $this->disciplineSingle->id,
            'active' => true,
        ]);

        $this->voterMulti = User::create([
            'name' => 'Dr Multi Voter',
            'email' => 'voter.multi@akademisains.gov.my',
            'password' => bcrypt('Password123!'),
            'role' => 'voting_user',
            'discipline_id' => $this->disciplineMulti->id,
            'active' => true,
        ]);

        $this->rankingService = app(RankingService::class);
    }

    /**
     * TEST 1: Discipline has 1 candidate.
     * - "My Ranking" hidden from nav
     * - "Start Ranking Exercise" / "Proceed My Ranking Board" hidden
     * - Candidate remains visible
     * - Ranking not required
     * - Discipline automatically considered complete
     * - Direct access to /ranking redirects with flash message
     */
    public function test_single_candidate_discipline_hides_ranking_and_is_automatically_complete(): void
    {
        $this->assertFalse($this->disciplineSingle->isRankingRequired());
        $this->assertTrue($this->disciplineSingle->isSingleCandidate());
        $this->assertEquals(1, $this->disciplineSingle->active_candidate_count);

        // Service recognizes ranking requirement is automatically satisfied
        $this->assertTrue($this->rankingService->isCompletedForUser($this->voterSingle, $this->exercise));

        // Visit voter dashboard
        $response = $this->actingAs($this->voterSingle)->get(route('dashboard'));
        $response->assertStatus(200);

        // UI assertions
        $response->assertDontSee('Start Ranking Exercise');
        $response->assertDontSee('Proceed My Ranking Board');
        $response->assertDontSee('Continue Ranking Exercise');
        $response->assertSee('1 candidate — Ranking not required');
        $response->assertSee('SATISFIED ✓');
        $response->assertSee('Professor ChM Dr Juan Joon Ching');

        // Navigation check: "My Ranking" should be hidden
        $response->assertDontSee('>My Ranking<', false);

        // Direct URL protection on /ranking
        $directRankingResponse = $this->actingAs($this->voterSingle)->get(route('ranking.index'));
        $directRankingResponse->assertRedirect(route('dashboard'));
        $directRankingResponse->assertSessionHas('info', 'This discipline has only one candidate. Ranking exercise is not required.');

        // Direct URL protection on /ranking/{discipline}
        $directParamResponse = $this->actingAs($this->voterSingle)->get(route('ranking.discipline', ['discipline' => $this->disciplineSingle->id]));
        $directParamResponse->assertRedirect(route('dashboard'));
        $directParamResponse->assertSessionHas('info', 'This discipline has only one candidate. Ranking exercise is not required.');

        // Direct POST to /ranking/submit should redirect to dashboard with flash message
        $submitResponse = $this->actingAs($this->voterSingle)->post(route('ranking.submit'), [
            'rankings' => [$this->candidate1->id => 1],
        ]);
        $submitResponse->assertRedirect(route('dashboard'));
        $submitResponse->assertSessionHas('info', 'This discipline has only one candidate. Ranking exercise is not required.');

        // My Submission receipt shows single-candidate status and candidate profile
        $receiptResponse = $this->actingAs($this->voterSingle)->get(route('my-submission'));
        $receiptResponse->assertStatus(200);
        $receiptResponse->assertSee('Ranking: Not Required (Single Candidate)');
        $receiptResponse->assertSee('Professor ChM Dr Juan Joon Ching');
    }

    /**
     * TEST 2: Discipline has 2 candidates.
     * - Existing ranking UI appears
     * - "My Ranking" appears
     * - "Start Ranking Exercise" appears
     * - Existing ranking validation applies
     */
    public function test_multi_candidate_discipline_requires_ranking_and_shows_ui(): void
    {
        $this->assertTrue($this->disciplineMulti->isRankingRequired());
        $this->assertFalse($this->disciplineMulti->isSingleCandidate());
        $this->assertEquals(2, $this->disciplineMulti->active_candidate_count);

        // Service recognizes ranking requirement is NOT completed yet
        $this->assertFalse($this->rankingService->isCompletedForUser($this->voterMulti, $this->exercise));

        // Visit voter dashboard
        $response = $this->actingAs($this->voterMulti)->get(route('dashboard'));
        $response->assertStatus(200);

        // UI assertions
        $response->assertSee('Start Ranking Exercise');
        $response->assertSee('My Ranking');
        $response->assertSee('NOT SUBMITTED');
        $response->assertDontSee('1 candidate — Ranking not required');

        // Direct access to /ranking returns 200 with ranking board
        $rankingResponse = $this->actingAs($this->voterMulti)->get(route('ranking.index'));
        $rankingResponse->assertStatus(200);
        $rankingResponse->assertSee('Professor Dr Alice Smith');
        $rankingResponse->assertSee('Professor Dr Bob Jones');

        // Validation applies: empty or invalid sequence is rejected
        $invalidSubmit = $this->actingAs($this->voterMulti)->post(route('ranking.submit'), [
            'rankings' => [
                $this->candidate2->id => 1,
                $this->candidate3->id => 1,
            ],
        ]);
        $invalidSubmit->assertSessionHasErrors('rankings');

        // Valid submission succeeds
        $validSubmit = $this->actingAs($this->voterMulti)->post(route('ranking.submit'), [
            'rankings' => [
                $this->candidate2->id => 1,
                $this->candidate3->id => 2,
            ],
        ]);
        $validSubmit->assertRedirect(route('my-submission'));
        $validSubmit->assertSessionHas('success');

        // After submission, marked complete
        $this->assertTrue($this->rankingService->isCompletedForUser($this->voterMulti, $this->exercise));
    }

    /**
     * TEST 3: Discipline has 3+ candidates.
     * - Existing ranking UI appears
     * - Existing ranking validation applies
     * - No change to current ranking behaviour
     */
    public function test_three_plus_candidates_discipline_operates_with_standard_ranking(): void
    {
        Candidate::create([
            'discipline_id' => $this->disciplineMulti->id,
            'candidate_name' => 'Professor Dr Carol White',
            'candidate_title' => 'Professor of Agronomy',
            'organisation' => 'Universiti Sains Malaysia',
            'active' => true,
            'display_order' => 3,
        ]);

        $this->assertEquals(3, $this->disciplineMulti->fresh()->active_candidate_count);
        $this->assertTrue($this->disciplineMulti->isRankingRequired());

        $response = $this->actingAs($this->voterMulti)->get(route('ranking.index'));
        $response->assertStatus(200);
        $response->assertSee('Professor Dr Carol White');
    }

    /**
     * TEST 4: A discipline changes from 1 candidate to 2 candidates.
     * - Ranking automatically becomes required
     * - Ranking options become visible
     * - Existing ranking workflow becomes available
     */
    public function test_dynamic_transition_from_one_candidate_to_two_candidates(): void
    {
        // Initially 1 candidate
        $this->assertFalse($this->disciplineSingle->isRankingRequired());

        // Add a second candidate to CS
        Candidate::create([
            'discipline_id' => $this->disciplineSingle->id,
            'candidate_name' => 'Professor Dr Second Chemist',
            'candidate_title' => 'Professor of Organic Chemistry',
            'organisation' => 'Universiti Teknologi Malaysia',
            'active' => true,
            'display_order' => 2,
        ]);

        // Refresh model check
        $this->assertTrue($this->disciplineSingle->fresh()->isRankingRequired());
        $this->assertFalse($this->disciplineSingle->fresh()->isSingleCandidate());
        $this->assertEquals(2, $this->disciplineSingle->fresh()->active_candidate_count);

        // Dashboard now requires ranking
        $response = $this->actingAs($this->voterSingle)->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Start Ranking Exercise');
        $response->assertSee('My Ranking');
        $response->assertDontSee('1 candidate — Ranking not required');

        // /ranking is accessible
        $rankingResponse = $this->actingAs($this->voterSingle)->get(route('ranking.index'));
        $rankingResponse->assertStatus(200);
        $rankingResponse->assertSee('Professor Dr Second Chemist');
    }

    /**
     * TEST 5: A discipline changes from 2 candidates to 1 candidate.
     * - Ranking automatically becomes not required
     * - Ranking options become hidden
     * - Overall exercise remains completable
     */
    public function test_dynamic_transition_from_two_candidates_to_one_candidate(): void
    {
        // Initially 2 candidates in BAES
        $this->assertTrue($this->disciplineMulti->isRankingRequired());

        // Deactivate candidate 3
        $this->candidate3->update(['active' => false]);

        // Candidate count is now 1
        $this->assertFalse($this->disciplineMulti->fresh()->isRankingRequired());
        $this->assertTrue($this->disciplineMulti->fresh()->isSingleCandidate());
        $this->assertEquals(1, $this->disciplineMulti->fresh()->active_candidate_count);

        // Dashboard hides ranking options and shows satisfied status
        $response = $this->actingAs($this->voterMulti)->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertDontSee('Start Ranking Exercise');
        $response->assertDontSee('>My Ranking<', false);
        $response->assertSee('1 candidate — Ranking not required');
        $response->assertSee('SATISFIED ✓');

        // /ranking redirects to dashboard
        $directResponse = $this->actingAs($this->voterMulti)->get(route('ranking.index'));
        $directResponse->assertRedirect(route('dashboard'));
        $directResponse->assertSessionHas('info', 'This discipline has only one candidate. Ranking exercise is not required.');

        // Exercise is considered complete for user
        $this->assertTrue($this->rankingService->isCompletedForUser($this->voterMulti, $this->exercise));
    }

    /**
     * TEST 6: Admin View and Reports for single-candidate discipline.
     * - Admin matrix displays "Ranking: Not Required (Single Candidate)"
     * - Candidate remains visible
     */
    public function test_admin_results_and_reports_reflect_single_candidate_status(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@akademisains.gov.my',
            'password' => bcrypt('AdminPassword123!'),
            'role' => 'administrator',
            'discipline_id' => $this->disciplineSingle->id,
            'active' => true,
        ]);

        // Admin Results Matrix for single-candidate discipline
        $response = $this->actingAs($admin)->get(route('admin.ranking-results.index', [
            'discipline_id' => $this->disciplineSingle->id,
            'exercise_id' => $this->exercise->id,
        ]));
        $response->assertStatus(200);
        $response->assertSee('Ranking: Not Required (Single Candidate)');
        $response->assertSee('Professor ChM Dr Juan Joon Ching');

        // CSV Export for single-candidate discipline
        $csvResponse = $this->actingAs($admin)->get(route('admin.reports.export-ranking-csv', [
            'discipline_id' => $this->disciplineSingle->id,
            'exercise_id' => $this->exercise->id,
        ]));
        $csvResponse->assertStatus(200);
        $this->assertStringContainsString('Ranking: Not Required (Single Candidate)', $csvResponse->getContent());
        $this->assertStringContainsString('Professor ChM Dr Juan Joon Ching', $csvResponse->getContent());
    }

    /**
     * TEST 7: Single candidate display in Discipline status page (/my-submission)
     * - Uses full profile layout
     * - Does NOT clamp or truncate fields 7, 8, 9 with line-clamp
     * - Uses fixed portrait photo frame (w-36/w-44) rather than stretched full-width
     * - Displays all candidate information fully
     */
    public function test_single_candidate_discipline_displays_full_unclamped_information_and_portrait_photo(): void
    {
        $this->candidate1->update([
            'area_of_expertise' => 'Nanotechnology & Catalysis with extensive multi-line description of research initiatives.',
            'qualifications_professional_memberships' => "BSc (Hons) Chemistry\nPhD Chemistry\nFellow of the Royal Society of Chemistry",
            'basis_of_recommendation' => 'Distinguished academic leader with profound global impact in materials science and renewable energy solutions.',
            'affiliation_to_asm' => "TRSM, 2024\nYSN-ASM, 2017-2020",
        ]);

        $response = $this->actingAs($this->voterSingle)->get(route('my-submission'));
        $response->assertStatus(200);

        // Header & breadcrumb reflects Discipline Status
        $response->assertSee('Discipline Status');
        $response->assertSee('Discipline Status: Candidate Profile');

        // Candidate information is completely visible
        $response->assertSee('Professor ChM Dr Juan Joon Ching');
        $response->assertSee('Professor of Nanotechnology');
        $response->assertSee('TRSM, 2024');
        $response->assertSee('YSN-ASM, 2017-2020');
        $response->assertSee('BSc (Hons) Chemistry');
        $response->assertSee('PhD Chemistry');
        $response->assertSee('Fellow of the Royal Society of Chemistry');
        $response->assertSee('Distinguished academic leader with profound global impact');

        // Verify that line-clamp is NOT used on the full candidate profile
        $content = $response->getContent();
        $this->assertStringNotContainsString('line-clamp-2', $content);
        $this->assertStringNotContainsString('line-clamp-3', $content);

        // Verify portrait frame class is present
        $this->assertStringContainsString('w-36', $content);
    }
}
