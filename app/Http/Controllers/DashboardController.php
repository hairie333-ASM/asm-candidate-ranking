<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Discipline;
use App\Models\DueDiligenceSubmission;
use App\Models\User;
use App\Services\DisciplineLandingPageService;
use App\Services\RankingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected RankingService $rankingService,
        protected DisciplineLandingPageService $landingPageService
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $exercise = $this->rankingService->getActiveExercise();

        // 1. Voting User Dashboard (Discipline-specific Landing Page)
        if ($user->isVotingUser() || ($user->hasDiscipline() && ! $user->isAdmin())) {
            $discipline = $user->discipline;
            $dossier = $this->landingPageService->getForDiscipline($discipline);
            $candidates = $this->rankingService->getEligibleCandidates($user);
            $candidateCount = $candidates->count();

            $isSingleCandidate = $candidateCount === 1;
            $isRankingRequired = $candidateCount > 1;

            $submission = $this->rankingService->getUserSubmission($user, $exercise);
            $isSubmitted = $submission && $submission->status === 'SUBMITTED';
            $rankedCount = $submission ? $submission->rankings->count() : 0;

            // Single candidate discipline automatically satisfies ranking requirement
            $isCompleted = $isSingleCandidate || $isSubmitted;

            return view('dashboard.voter', [
                'user' => $user,
                'discipline' => $discipline,
                'dossier' => $dossier,
                'candidateCount' => $candidateCount,
                'candidates' => $candidates,
                'singleCandidate' => $isSingleCandidate ? $candidates->first() : null,
                'isSingleCandidate' => $isSingleCandidate,
                'isRankingRequired' => $isRankingRequired,
                'isCompleted' => $isCompleted,
                'submission' => $submission,
                'isSubmitted' => $isSubmitted,
                'rankedCount' => $rankedCount,
                'exercise' => $exercise,
            ]);
        }

        // 2. Administrator Dashboard
        if ($user->isAdmin()) {
            if ($request->filled('preview_discipline')) {
                $discipline = Discipline::find((int) $request->input('preview_discipline'))
                    ?? Discipline::where('discipline_name', 'LIKE', '%'.$request->input('preview_discipline').'%')->first()
                    ?? Discipline::first();

                $dossier = $this->landingPageService->getForDiscipline($discipline);
                $candidates = $discipline ? $discipline->candidates()->where('active', true)->get() : collect();
                $candidateCount = $candidates->count();
                $isSingleCandidate = $candidateCount === 1;

                return view('dashboard.voter', [
                    'user' => $user,
                    'discipline' => $discipline,
                    'dossier' => $dossier,
                    'candidateCount' => $candidateCount,
                    'candidates' => $candidates,
                    'singleCandidate' => $isSingleCandidate ? $candidates->first() : null,
                    'isSingleCandidate' => $isSingleCandidate,
                    'isRankingRequired' => $candidateCount > 1,
                    'isCompleted' => false,
                    'submission' => null,
                    'isSubmitted' => false,
                    'rankedCount' => 0,
                    'exercise' => $exercise,
                    'isAdminPreview' => true,
                ]);
            }

            return redirect()->route('admin.dashboard');
        }

        // 3. Reviewer Dashboard
        $disciplines = Discipline::withCount(['candidates'])->orderBy('display_order')->get();
        $totalCandidates = Candidate::where('active', true)->count();
        $totalDueDiligence = DueDiligenceSubmission::count();

        return view('dashboard.reviewer', [
            'user' => $user,
            'exercise' => $exercise,
            'disciplines' => $disciplines,
            'totalCandidates' => $totalCandidates,
            'totalDueDiligence' => $totalDueDiligence,
        ]);
    }
}
