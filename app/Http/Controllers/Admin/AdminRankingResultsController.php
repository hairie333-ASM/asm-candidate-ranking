<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discipline;
use App\Models\RankingExercise;
use App\Models\RankingSubmission;
use App\Models\User;
use App\Services\RankingService;
use App\Services\ReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminRankingResultsController extends Controller
{
    public function __construct(
        protected ReportService $reportService,
        protected RankingService $rankingService
    ) {}

    public function index(Request $request): View
    {
        $disciplines = Discipline::where('active', true)->orderBy('display_order')->get();
        $selectedDisciplineId = (int) $request->input('discipline_id', $disciplines->first()?->id ?? 1);

        $exercises = RankingExercise::latest()->get();
        $selectedExerciseId = $request->filled('exercise_id')
            ? (int) $request->input('exercise_id')
            : ($exercises->firstWhere('status', 'Open')?->id ?? $exercises->first()?->id);

        $resultsData = $this->reportService->getDisciplineResults($selectedDisciplineId, $selectedExerciseId);

        $submissions = RankingSubmission::with(['user', 'discipline'])
            ->where('discipline_id', $selectedDisciplineId)
            ->when($selectedExerciseId, fn ($q) => $q->where('exercise_id', $selectedExerciseId))
            ->get();

        return view('admin.ranking-results.index', [
            'disciplines' => $disciplines,
            'selectedDisciplineId' => $selectedDisciplineId,
            'exercises' => $exercises,
            'selectedExerciseId' => $selectedExerciseId,
            'results' => $resultsData,
            'submissions' => $submissions,
        ]);
    }

    public function reopen(Request $request, RankingSubmission $submission): RedirectResponse
    {
        /** @var User $admin */
        $admin = $request->user();

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $this->rankingService->reopenSubmission($admin, $submission, $validated['reason']);

        return back()->with('success', "Submission for {$submission->user->name} has been reopened and set to draft.");
    }
}
