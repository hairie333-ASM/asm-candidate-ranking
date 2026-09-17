<?php

namespace App\Http\Controllers;

use App\Http\Requests\DueDiligenceRequest;
use App\Models\Candidate;
use App\Models\Discipline;
use App\Models\DueDiligenceSubmission;
use App\Models\SupportingDocument;
use App\Models\User;
use App\Services\AuditService;
use App\Services\DueDiligenceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DueDiligenceController extends Controller
{
    public function __construct(
        protected DueDiligenceService $dueDiligenceService,
        protected AuditService $auditService
    ) {}

    /**
     * Show due diligence hub.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $query = Candidate::with('discipline')
            ->where('active', true);

        if ($request->filled('q')) {
            $keyword = '%'.trim($request->input('q')).'%';
            $query->where(function ($sub) use ($keyword) {
                $sub->where('candidate_name', 'like', $keyword)
                    ->orWhere('organisation', 'like', $keyword);
            });
        }

        if ($request->filled('discipline_id') && $request->input('discipline_id') !== 'all') {
            $query->where('discipline_id', (int) $request->input('discipline_id'));
        }

        $candidates = $query->orderBy('discipline_id')->orderBy('display_order')->paginate(15)->withQueryString();
        $disciplines = Discipline::where('active', true)->orderBy('display_order')->get();

        // User's own past submissions
        $mySubmissions = DueDiligenceSubmission::with(['candidate.discipline', 'category', 'supportingDocuments'])
            ->where('user_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        return view('due-diligence.index', [
            'candidates' => $candidates,
            'disciplines' => $disciplines,
            'mySubmissions' => $mySubmissions,
            'filters' => $request->only(['q', 'discipline_id']),
        ]);
    }

    /**
     * Show form to submit due diligence for a specific candidate.
     * Accessible across ALL disciplines!
     */
    public function create(Candidate $candidate): View
    {
        $candidate->load('discipline');
        $categories = $this->dueDiligenceService->getCategories();

        return view('due-diligence.create', [
            'candidate' => $candidate,
            'categories' => $categories,
        ]);
    }

    /**
     * Store new due diligence comment and optional documents.
     */
    public function store(DueDiligenceRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $candidateId = (int) $request->input('candidate_id');
        $categoryId = (int) $request->input('category_id');
        $comment = $request->input('comment');
        $files = $request->file('documents', []);
        $referenceData = $request->only([
            'reference_1_name',
            'reference_1_designation',
            'reference_1_organisation',
            'reference_1_contact_number',
            'reference_1_email',
            'reference_2_name',
            'reference_2_designation',
            'reference_2_organisation',
            'reference_2_contact_number',
            'reference_2_email',
        ]);

        $this->dueDiligenceService->submit($user, $candidateId, $categoryId, $comment, $files, $referenceData);

        return redirect()->route('due-diligence.index')
            ->with('success', 'Your due diligence assessment has been submitted successfully.');
    }

    /**
     * Securely download or view private supporting document.
     */
    public function downloadDocument(SupportingDocument $document, Request $request): BinaryFileResponse
    {
        /** @var User $user */
        $user = $request->user();
        $document->load('submission');

        if (! Gate::allows('viewDocument', $document)) {
            abort(403, 'Unauthorized access to confidential document.');
        }

        if (! Storage::disk('local')->exists($document->storage_path)) {
            abort(404, 'The requested document file could not be found on storage.');
        }

        $this->auditService->log(
            action: 'File Accessed',
            recordType: 'SupportingDocument',
            recordId: $document->id,
            description: "User {$user->email} downloaded/accessed file {$document->original_filename}",
            user: $user
        );

        $path = Storage::disk('local')->path($document->storage_path);

        return response()->download($path, $document->original_filename, [
            'Content-Type' => $document->mime_type,
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
