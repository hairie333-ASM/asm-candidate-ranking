<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RankingExercise;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminExerciseController extends Controller
{
    public function __construct(
        protected AuditService $auditService
    ) {}

    public function index(): View
    {
        $exercises = RankingExercise::withCount('rankingSubmissions')
            ->latest()
            ->paginate(15);

        return view('admin.exercises.index', [
            'exercises' => $exercises,
        ]);
    }

    public function create(): View
    {
        return view('admin.exercises.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'exercise_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_datetime' => ['nullable', 'date'],
            'end_datetime' => ['nullable', 'date', 'after_or_equal:start_datetime'],
            'status' => ['required', 'in:Draft,Open,Closed'],
            'allow_resubmission' => ['boolean'],
        ]);

        $exercise = RankingExercise::create([
            'exercise_name' => $validated['exercise_name'],
            'description' => $validated['description'],
            'start_datetime' => $validated['start_datetime'],
            'end_datetime' => $validated['end_datetime'],
            'status' => $validated['status'],
            'allow_resubmission' => $request->boolean('allow_resubmission'),
        ]);

        $this->auditService->log(
            action: 'Exercise Created',
            recordType: 'RankingExercise',
            recordId: $exercise->id,
            description: "Created exercise: {$exercise->exercise_name} (Status: {$exercise->status})"
        );

        return redirect()->route('admin.exercises.index')->with('success', "Exercise '{$exercise->exercise_name}' created successfully.");
    }

    public function edit(RankingExercise $exercise): View
    {
        return view('admin.exercises.edit', [
            'exercise' => $exercise,
        ]);
    }

    public function update(Request $request, RankingExercise $exercise): RedirectResponse
    {
        $validated = $request->validate([
            'exercise_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_datetime' => ['nullable', 'date'],
            'end_datetime' => ['nullable', 'date', 'after_or_equal:start_datetime'],
            'status' => ['required', 'in:Draft,Open,Closed'],
            'allow_resubmission' => ['boolean'],
        ]);

        $oldStatus = $exercise->status;

        $exercise->update([
            'exercise_name' => $validated['exercise_name'],
            'description' => $validated['description'],
            'start_datetime' => $validated['start_datetime'],
            'end_datetime' => $validated['end_datetime'],
            'status' => $validated['status'],
            'allow_resubmission' => $request->boolean('allow_resubmission'),
        ]);

        if ($oldStatus !== $exercise->status) {
            $this->auditService->log(
                action: $exercise->status === 'Open' ? 'Exercise Opened' : 'Exercise Closed',
                recordType: 'RankingExercise',
                recordId: $exercise->id,
                description: "Exercise status changed from {$oldStatus} to {$exercise->status}"
            );
        }

        return redirect()->route('admin.exercises.index')->with('success', "Exercise '{$exercise->exercise_name}' updated successfully.");
    }
}
