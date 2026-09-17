<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Discipline;
use App\Models\DueDiligenceSubmission;
use App\Models\User;
use App\Services\RankingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected RankingService $rankingService
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $exercise = $this->rankingService->getActiveExercise();

        // 1. Voting User Dashboard
        if ($user->isVotingUser()) {
            $discipline = $user->discipline;
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
