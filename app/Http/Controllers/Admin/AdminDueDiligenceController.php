<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discipline;
use App\Models\DueDiligenceCategory;
use App\Models\DueDiligenceSubmission;
use App\Models\SupportingDocument;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class AdminDueDiligenceController extends Controller
{
    public function __construct(
        protected AuditService $auditService
    ) {}

    public function index(Request $request): View
    {
        $query = DueDiligenceSubmission::with([
            'candidate.discipline',
            'category',
            'user',
            'userDiscipline',
            'supportingDocuments',
        ]);

        if ($request->filled('discipline_id') && $request->input('discipline_id') !== 'all') {
            $query->whereHas('candidate', fn ($q) => $q->where('discipline_id', (int) $request->input('discipline_id')));
        }

        if ($request->filled('category_id') && $request->input('category_id') !== 'all') {
            $query->where('category_id', (int) $request->input('category_id'));
        }

        if ($request->filled('q')) {
            $keyword = '%'.trim($request->input('q')).'%';
            $query->where(function ($sub) use ($keyword) {
                $sub->where('comment', 'like', $keyword)
                    ->orWhereHas('candidate', fn ($cq) => $cq->where('candidate_name', 'like', $keyword))
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', $keyword));
            });
        }

        $submissions = $query->latest()->paginate(20)->withQueryString();
        $disciplines = Discipline::where('active', true)->orderBy('display_order')->get();
        $categories = DueDiligenceCategory::where('active', true)->orderBy('display_order')->get();

        $metrics = [
            'total_submissions' => DueDiligenceSubmission::count(),
            'total_candidates' => DueDiligenceSubmission::distinct('candidate_id')->count('candidate_id'),
            'total_documents' => SupportingDocument::count(),
            'total_disciplines' => Discipline::whereHas('candidates.dueDiligenceSubmissions')->count(),
        ];

        return view('admin.due-diligence.index', [
            'submissions' => $submissions,
            'disciplines' => $disciplines,
            'categories' => $categories,
            'filters' => $request->only(['discipline_id', 'category_id', 'q']),
            'metrics' => $metrics,
        ]);
    }

    public function show(DueDiligenceSubmission $submission, Request $request): View
    {
        $submission->load([
            'candidate.discipline',
            'category',
            'user.discipline',
            'userDiscipline',
            'supportingDocuments',
        ]);

        $this->auditService->log(
            action: 'Due Diligence Inspected',
            recordType: 'DueDiligenceSubmission',
            recordId: $submission->id,
            description: "Admin viewed due diligence submission #{$submission->id} for candidate {$submission->candidate->candidate_name}",
            user: $request->user()
        );

        return view('admin.due-diligence.show', [
            'submission' => $submission,
        ]);
    }

    public function previewDocument(SupportingDocument $document, Request $request): Response
    {
        $document->load('submission.candidate');

        if (! Storage::disk('local')->exists($document->storage_path)) {
            abort(404, 'The requested document file could not be found in storage.');
        }

        $this->auditService->log(
            action: 'Document Previewed',
            recordType: 'SupportingDocument',
            recordId: $document->id,
            description: "Admin previewed document '{$document->original_filename}' for submission #{$document->due_diligence_submission_id}",
            user: $request->user()
        );

        $path = Storage::disk('local')->path($document->storage_path);

        return response()->file($path, [
            'Content-Type' => $document->mime_type ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="'.addslashes($document->original_filename).'"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }

    public function downloadDocument(SupportingDocument $document, Request $request): BinaryFileResponse
    {
        $document->load('submission.candidate');

        if (! Storage::disk('local')->exists($document->storage_path)) {
            abort(404, 'The requested document file could not be found in storage.');
        }

        $this->auditService->log(
            action: 'Document Downloaded',
            recordType: 'SupportingDocument',
            recordId: $document->id,
            description: "Admin downloaded document '{$document->original_filename}' for submission #{$document->due_diligence_submission_id}",
            user: $request->user()
        );

        $path = Storage::disk('local')->path($document->storage_path);

        return response()->download($path, $document->original_filename, [
            'Content-Type' => $document->mime_type ?: 'application/octet-stream',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }
}
