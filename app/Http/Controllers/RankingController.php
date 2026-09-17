<?php

namespace App\Http\Controllers;

use App\Http\Requests\RankingSubmissionRequest;
use App\Models\Candidate;
use App\Models\Discipline;
use App\Models\User;
use App\Services\RankingService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RankingController extends Controller
{
    public function __construct(
        protected RankingService $rankingService
    ) {}

    /**
     * Show the ranking board for the user's assigned discipline.
     */
    public function index(Request $request): View|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (! $user->discipline_id && ! $user->isAdmin()) {
            abort(403, 'You do not have an assigned discipline to participate in candidate ranking.');
        }

        $exercise = $this->rankingService->getActiveExercise();
        if (! $exercise) {
            return view('ranking.closed', [
                'message' => 'There is currently no active ranking exercise available.',
            ]);
        }

        $submission = $this->rankingService->getUserSubmission($user, $exercise);

        // If already submitted and resubmission is not permitted, redirect to submission receipt
        if ($submission && $submission->status === 'SUBMITTED' && ! $exercise->allow_resubmission) {
            return redirect()->route('my-submission')
                ->with('info', 'You have already finalized and submitted your ranking for this exercise.');
        }

        $candidates = $this->rankingService->getEligibleCandidates($user);
        $totalCandidates = $candidates->count();

        // Single-candidate discipline: ranking is not required. Redirect with informative message.
        if ($totalCandidates === 1) {
            return redirect()->route('dashboard')
                ->with('info', 'This discipline has only one candidate. Ranking exercise is not required.');
        }

        // Build existing rank map if resubmitting or drafting
        $existingRanks = [];
        if ($submission) {
            foreach ($submission->rankings as $r) {
                $existingRanks[$r->candidate_id] = $r->ranking_number;
            }
        }

        $candidatesData = $candidates->map(function ($c) use ($existingRanks) {
            return [
                'id' => $c->id,
                'name' => $c->candidate_name,
                'title' => $c->candidate_title,
                'organisation' => $c->organisation,
                'photo_url' => $c->photo_url ?: 'https://ui-avatars.com/api/?name='.urlencode($c->candidate_name).'&background=0D9488&color=fff&size=256',
                'area_of_expertise' => $c->area_of_expertise,
                'qualifications' => $c->qualifications,
                'professional_memberships' => $c->professional_memberships,
                'short_description' => $c->short_description,
                'basis_of_recommendation' => $c->basis_of_recommendation,
                'nomination_form_url' => $c->nomination_form_url,
                'existing_rank' => $existingRanks[$c->id] ?? null,
            ];
        });

        return view('ranking.index', [
            'user' => $user,
            'discipline' => $user->discipline,
            'exercise' => $exercise,
            'candidates' => $candidates,
            'candidatesData' => $candidatesData,
            'candidatesJson' => $candidatesData->toJson(),
            'existingRanks' => $existingRanks,
            'totalCandidates' => $totalCandidates,
            'submission' => $submission,
        ]);
    }

    /**
     * Handle direct route access for a discipline ranking (e.g. /ranking/{discipline}).
     */
    public function showDisciplineRanking(Request $request, string $discipline): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        // Resolve discipline by ID or code
        $disc = is_numeric($discipline)
            ? Discipline::find($discipline)
            : Discipline::where('discipline_name', 'LIKE', "{$discipline}%")->first();

        $disc = $disc ?? $user->discipline;

        if ($disc && ! $disc->isRankingRequired()) {
            return redirect()->route('dashboard')
                ->with('info', 'This discipline has only one candidate. Ranking exercise is not required.');
        }

        return redirect()->route('ranking.index');
    }

    /**
     * Authoritatively validate and finalize rankings.
     */
    public function submit(RankingSubmissionRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $rankings = $request->input('rankings', []);

        // Security boundary: If any submitted candidate belongs to another discipline, enforce 403
        $submittedCandidateIds = array_keys($rankings);
        if (! empty($submittedCandidateIds)) {
            $mismatched = Candidate::whereIn('id', $submittedCandidateIds)
                ->where('discipline_id', '!=', $user->discipline_id)
                ->exists();

            if ($mismatched) {
                $exercise = $this->rankingService->getActiveExercise();
                if ($exercise) {
                    try {
                        $this->rankingService->validateAndSubmit($user, $exercise, $rankings);
                    } catch (AuthorizationException $e) {
                        abort(403, $e->getMessage());
                    }
                }
            }
        }

        $candidates = $this->rankingService->getEligibleCandidates($user);
        if ($candidates->count() <= 1) {
            return redirect()->route('dashboard')
                ->with('info', 'This discipline has only one candidate. Ranking exercise is not required.');
        }

        $exercise = $this->rankingService->getActiveExercise();
        if (! $exercise) {
            return back()->withErrors(['exercise' => 'The ranking exercise is not currently active.']);
        }

        try {
            $rankings = $request->input('rankings', []);
            $this->rankingService->validateAndSubmit($user, $exercise, $rankings);

            return redirect()->route('my-submission')
                ->with('success', "Your ranking for {$user->discipline?->discipline_name} has been submitted successfully!");
        } catch (AuthorizationException $e) {
            abort(403, $e->getMessage());
        } catch (ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        }
    }

    /**
     * Display the user's submitted ranking receipt.
     */
    public function mySubmission(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $exercise = $this->rankingService->getActiveExercise();

        $submission = $this->rankingService->getUserSubmission($user, $exercise);
        $candidates = $this->rankingService->getEligibleCandidates($user);
        $isSingleCandidate = $candidates->count() === 1;
        $singleCandidate = $isSingleCandidate ? $candidates->first() : null;

        return view('ranking.my-submission', [
            'user' => $user,
            'discipline' => $user->discipline,
            'exercise' => $exercise,
            'submission' => $submission,
            'isSingleCandidate' => $isSingleCandidate,
            'singleCandidate' => $singleCandidate,
        ]);
    }
}
