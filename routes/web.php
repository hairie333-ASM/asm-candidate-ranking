<?php

use App\Http\Controllers\Admin\AdminAuditLogController;
use App\Http\Controllers\Admin\AdminCandidateController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminDisciplineController;
use App\Http\Controllers\Admin\AdminDueDiligenceController;
use App\Http\Controllers\Admin\AdminExerciseController;
use App\Http\Controllers\Admin\AdminRankingResultsController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DueDiligenceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RankingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('pages.home');
})->name('home');

Route::get('/other-information', function () {
    return view('pages.other-information');
})->name('other-information');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Candidate API endpoint for interactive profile modal
Route::get('/api/candidates/{candidate}', [CandidateController::class, 'apiShow'])->name('api.candidates.show');

// Reliable photo asset delivery routes for candidate photos
Route::get('/storage/photos/{filename}', function (string $filename) {
    $candidates = [
        public_path('photos/'.$filename),
        storage_path('app/public/photos/'.$filename),
    ];

    foreach ($candidates as $path) {
        if (file_exists($path)) {
            return response()->file($path);
        }
    }

    abort(404);
})->where('filename', '[A-Za-z0-9\-_.]+')->name('storage.photos.show');

Route::get('/photos/{filename}', function (string $filename) {
    $path = public_path('photos/'.$filename);
    if (file_exists($path)) {
        return response()->file($path);
    }

    $storagePath = storage_path('app/public/photos/'.$filename);
    if (file_exists($storagePath)) {
        return response()->file($storagePath);
    }

    abort(404);
})->where('filename', '[A-Za-z0-9\-_.]+')->name('photos.show');

/*
|--------------------------------------------------------------------------
| Authenticated Protected Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'user.active'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Candidate Ranking (Restricted strictly to user's assigned discipline)
    Route::middleware('user.discipline')->group(function () {
        Route::get('/ranking', [RankingController::class, 'index'])->name('ranking.index');
        Route::get('/ranking/{discipline}', [RankingController::class, 'showDisciplineRanking'])->name('ranking.discipline');
        Route::post('/ranking/submit', [RankingController::class, 'submit'])->name('ranking.submit');
    });
    Route::get('/my-submission', [RankingController::class, 'mySubmission'])->name('my-submission');

    // Candidate Search & View (Accessible across ALL 8 disciplines)
    Route::get('/candidates', [CandidateController::class, 'index'])->name('candidates.index');
    Route::get('/candidates/{candidate}', [CandidateController::class, 'show'])->name('candidates.show');

    // Due Diligence (Accessible across ALL 8 disciplines)
    Route::get('/due-diligence', [DueDiligenceController::class, 'index'])->name('due-diligence.index');
    Route::get('/due-diligence/candidate/{candidate}', [DueDiligenceController::class, 'create'])->name('due-diligence.create');
    Route::post('/due-diligence', [DueDiligenceController::class, 'store'])->name('due-diligence.store');
    Route::get('/due-diligence/document/{document}', [DueDiligenceController::class, 'downloadDocument'])->name('due-diligence.download');

    // User Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Selection Process
    Route::get('/selection-process', function () {
        return view('pages.selection-process');
    })->name('selection-process');

    // Evaluation Rubric
    Route::get('/evaluation-rubric', function () {
        return view('pages.evaluation-rubric');
    })->name('evaluation-rubric');

    /*
    |--------------------------------------------------------------------------
    | Administrator Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        // User Management
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/toggle-active', [AdminUserController::class, 'toggleActive'])->name('users.toggle-active');
        Route::post('/users/{user}/reset-password', [AdminUserController::class, 'resetPassword'])->name('users.reset-password');

        // Candidate Management
        Route::get('/candidates', [AdminCandidateController::class, 'index'])->name('candidates.index');
        Route::get('/candidates/create', [AdminCandidateController::class, 'create'])->name('candidates.create');
        Route::post('/candidates', [AdminCandidateController::class, 'store'])->name('candidates.store');
        Route::get('/candidates/{candidate}/edit', [AdminCandidateController::class, 'edit'])->name('candidates.edit');
        Route::put('/candidates/{candidate}', [AdminCandidateController::class, 'update'])->name('candidates.update');
        Route::patch('/candidates/{candidate}/toggle-active', [AdminCandidateController::class, 'toggleActive'])->name('candidates.toggle-active');

        // Discipline Management
        Route::get('/disciplines', [AdminDisciplineController::class, 'index'])->name('disciplines.index');
        Route::get('/disciplines/create', [AdminDisciplineController::class, 'create'])->name('disciplines.create');
        Route::post('/disciplines', [AdminDisciplineController::class, 'store'])->name('disciplines.store');
        Route::get('/disciplines/{discipline}/edit', [AdminDisciplineController::class, 'edit'])->name('disciplines.edit');
        Route::put('/disciplines/{discipline}', [AdminDisciplineController::class, 'update'])->name('disciplines.update');
        Route::patch('/disciplines/{discipline}/toggle-active', [AdminDisciplineController::class, 'toggleActive'])->name('disciplines.toggle-active');

        // Exercise Management
        Route::get('/exercises', [AdminExerciseController::class, 'index'])->name('exercises.index');
        Route::get('/exercises/create', [AdminExerciseController::class, 'create'])->name('exercises.create');
        Route::post('/exercises', [AdminExerciseController::class, 'store'])->name('exercises.store');
        Route::get('/exercises/{exercise}/edit', [AdminExerciseController::class, 'edit'])->name('exercises.edit');
        Route::put('/exercises/{exercise}', [AdminExerciseController::class, 'update'])->name('exercises.update');

        // Ranking Results & Reopening
        Route::get('/ranking-results', [AdminRankingResultsController::class, 'index'])->name('ranking-results.index');
        Route::post('/ranking-results/reopen/{submission}', [AdminRankingResultsController::class, 'reopen'])->name('ranking-results.reopen');

        // Due Diligence Review
        Route::get('/due-diligence', [AdminDueDiligenceController::class, 'index'])->name('due-diligence.index');

        // Reports & Exports
        Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export-ranking-csv', [AdminReportController::class, 'exportRankingCsv'])->name('reports.export-ranking-csv');
        Route::get('/reports/export-due-diligence-csv', [AdminReportController::class, 'exportDueDiligenceCsv'])->name('reports.export-due-diligence-csv');
        Route::get('/reports/print-ranking', [AdminReportController::class, 'printRanking'])->name('reports.print-ranking');

        // Audit Logs
        Route::get('/audit-logs', [AdminAuditLogController::class, 'index'])->name('audit-logs.index');
    });
});
