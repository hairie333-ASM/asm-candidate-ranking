<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discipline;
use App\Models\RankingExercise;
use App\Services\AuditService;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class AdminReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService,
        protected AuditService $auditService
    ) {}

    public function index(): View
    {
        $disciplines = Discipline::where('active', true)->orderBy('display_order')->get();
        $exercises = RankingExercise::latest()->get();

        return view('admin.reports.index', [
            'disciplines' => $disciplines,
            'exercises' => $exercises,
        ]);
    }

    public function exportRankingCsv(Request $request): Response
    {
        $disciplineId = (int) $request->input('discipline_id', 1);
        $exerciseId = $request->filled('exercise_id') ? (int) $request->input('exercise_id') : null;

        $csv = $this->reportService->generateRankingCsv($disciplineId, $exerciseId);
        $discipline = Discipline::find($disciplineId);
        $filename = 'asm_ranking_discipline_'.$disciplineId.'_'.date('Ymd_His').'.csv';

        $this->auditService->log(
            action: 'Report Generated: Ranking CSV',
            recordType: 'Discipline',
            recordId: $disciplineId,
            description: "Exported ranking CSV for {$discipline?->discipline_name}"
        );

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function exportDueDiligenceCsv(Request $request): Response
    {
        $disciplineId = $request->filled('discipline_id') && $request->input('discipline_id') !== 'all'
            ? (int) $request->input('discipline_id')
            : null;

        $csv = $this->reportService->generateDueDiligenceCsv($disciplineId);
        $filename = 'asm_due_diligence_report_'.date('Ymd_His').'.csv';

        $this->auditService->log(
            action: 'Report Generated: Due Diligence CSV',
            description: 'Exported complete due diligence CSV report'
        );

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function printRanking(Request $request): View
    {
        $disciplineId = (int) $request->input('discipline_id', 1);
        $exerciseId = $request->filled('exercise_id') ? (int) $request->input('exercise_id') : null;

        $results = $this->reportService->getDisciplineResults($disciplineId, $exerciseId);

        return view('admin.reports.print-ranking', [
            'results' => $results,
        ]);
    }
}
