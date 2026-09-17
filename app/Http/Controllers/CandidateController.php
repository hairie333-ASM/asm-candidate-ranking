<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Discipline;
use App\Services\CandidateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CandidateController extends Controller
{
    public function __construct(
        protected CandidateService $candidateService
    ) {}

    /**
     * Search and list candidates across all 8 disciplines.
     */
    public function index(Request $request): View
    {
        $filters = [
            'q' => $request->query('q'),
            'discipline_id' => $request->query('discipline_id'),
        ];

        $candidates = $this->candidateService->search($filters, 12);
        $disciplines = Discipline::where('active', true)->orderBy('display_order')->get();

        return view('candidates.index', [
            'candidates' => $candidates,
            'disciplines' => $disciplines,
            'filters' => $filters,
        ]);
    }

    /**
     * Candidate profile page is replaced by interactive candidate modal.
     * Direct visits redirect to Due Diligence.
     */
    public function show(Candidate $candidate): RedirectResponse
    {
        return redirect()->route('due-diligence.index');
    }

    /**
     * JSON API endpoint for Candidate Information Modal.
     * Route: GET /api/candidates/{candidate}
     */
    public function apiShow(Candidate $candidate): JsonResponse
    {
        $candidate->load('discipline');

        return response()->json(
            $this->candidateService->formatForModal($candidate)
        );
    }
}
