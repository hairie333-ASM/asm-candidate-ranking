<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Candidate;
use App\Models\Discipline;
use App\Models\DueDiligenceSubmission;
use App\Models\RankingExercise;
use App\Models\RankingSubmission;
use App\Models\User;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $exercise = RankingExercise::where('status', 'Open')->latest()->first() ?? RankingExercise::latest()->first();

        $stats = [
            'total_users' => User::count(),
            'total_voters' => User::where('role', 'voting_user')->count(),
            'total_candidates' => Candidate::count(),
            'total_disciplines' => Discipline::count(),
            'total_submissions' => $exercise ? RankingSubmission::where('exercise_id', $exercise->id)->where('status', 'SUBMITTED')->count() : 0,
            'total_due_diligence' => DueDiligenceSubmission::count(),
        ];

        $disciplines = Discipline::withCount(['candidates', 'users'])
            ->with(['rankingSubmissions' => function ($q) use ($exercise) {
                if ($exercise) {
                    $q->where('exercise_id', $exercise->id)->where('status', 'SUBMITTED');
                }
            }])
            ->orderBy('display_order')
            ->get();

        $recentLogs = AuditLog::with('user')->latest()->take(10)->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'exercise' => $exercise,
            'disciplines' => $disciplines,
            'recentLogs' => $recentLogs,
        ]);
    }
}
