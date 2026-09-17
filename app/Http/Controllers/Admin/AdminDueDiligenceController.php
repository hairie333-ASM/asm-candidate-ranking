<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discipline;
use App\Models\DueDiligenceCategory;
use App\Models\DueDiligenceSubmission;
use App\Models\SupportingDocument;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use ZipArchive;

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

    public function destroy(DueDiligenceSubmission $submission, Request $request): RedirectResponse
    {
        $submissionId = $submission->id;
        $candidateName = $submission->candidate?->candidate_name ?? 'Candidate #'.$submission->candidate_id;

        DB::transaction(function () use ($submission, $request, $submissionId, $candidateName) {
            $documents = $submission->supportingDocuments;

            // Clean up files from disk
            foreach ($documents as $doc) {
                $filePath = $this->resolveFilePath($doc);
                if ($filePath && file_exists($filePath)) {
                    @unlink($filePath);
                }
                if (Storage::disk('local')->exists($doc->storage_path)) {
                    Storage::disk('local')->delete($doc->storage_path);
                }
            }

            // Remove submission directories if created
            Storage::disk('local')->deleteDirectory('private/due_diligence/'.$submissionId);
            Storage::disk('local')->deleteDirectory('due_diligence/'.$submissionId);

            // Delete database submission (foreign keys cascade to supporting_documents)
            $submission->delete();

            $this->auditService->log(
                action: 'Due Diligence Deleted',
                recordType: 'DueDiligenceSubmission',
                recordId: $submissionId,
                description: "Admin deleted due diligence submission #{$submissionId} for candidate {$candidateName}",
                user: $request->user()
            );
        });

        return redirect()->route('admin.due-diligence.index')
            ->with('success', "Due diligence submission #{$submissionId} for {$candidateName} has been permanently deleted.");
    }

    public function downloadZip(DueDiligenceSubmission $submission, Request $request): Response
    {
        $submission->load(['supportingDocuments', 'candidate']);

        $documents = $submission->supportingDocuments;

        if ($documents->isEmpty()) {
            return back()->with('error', 'No supporting documents attached to this due diligence submission.');
        }

        $candidateSlug = Str::slug($submission->candidate->candidate_name ?? 'candidate');
        $zipFilename = "due-diligence-sub-{$submission->id}-{$candidateSlug}-documents.zip";
        $tempZipPath = tempnam(sys_get_temp_dir(), 'dd_zip_').'.zip';

        $zip = new ZipArchive;
        if ($zip->open($tempZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return back()->with('error', 'Unable to create ZIP archive on the server.');
        }

        $addedCount = 0;
        $usedFilenames = [];

        foreach ($documents as $index => $doc) {
            $filePath = $this->resolveFilePath($doc);
            if ($filePath && file_exists($filePath)) {
                $filename = $doc->original_filename ?: ('attachment_'.($index + 1));

                // Avoid duplicate filenames inside zip
                if (isset($usedFilenames[$filename])) {
                    $usedFilenames[$filename]++;
                    $info = pathinfo($filename);
                    $ext = ! empty($info['extension']) ? '.'.$info['extension'] : '';
                    $nameOnly = $info['filename'] ?? 'file';
                    $filename = "{$nameOnly}_({$usedFilenames[$filename]}){$ext}";
                } else {
                    $usedFilenames[$filename] = 1;
                }

                $zip->addFile($filePath, $filename);
                $addedCount++;
            }
        }

        $zip->close();

        if ($addedCount === 0) {
            @unlink($tempZipPath);

            return back()->with('error', 'None of the attached documents could be located on server storage to package into ZIP.');
        }

        $this->auditService->log(
            action: 'Document ZIP Downloaded',
            recordType: 'DueDiligenceSubmission',
            recordId: $submission->id,
            description: "Admin downloaded ZIP package containing {$addedCount} document(s) for submission #{$submission->id}",
            user: $request->user()
        );

        return response()->download($tempZipPath, $zipFilename, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    public function previewDocument(SupportingDocument $document, Request $request): Response
    {
        $document->load('submission.candidate');

        $path = $this->resolveFilePath($document);

        if (! $path || ! file_exists($path)) {
            return back()->with('error', "Document '{$document->original_filename}' could not be located on the server filesystem.");
        }

        $this->auditService->log(
            action: 'Document Previewed',
            recordType: 'SupportingDocument',
            recordId: $document->id,
            description: "Admin previewed document '{$document->original_filename}' for submission #{$document->due_diligence_submission_id}",
            user: $request->user()
        );

        return response()->file($path, [
            'Content-Type' => $document->mime_type ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="'.addslashes($document->original_filename).'"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }

    public function downloadDocument(SupportingDocument $document, Request $request): Response
    {
        $document->load('submission.candidate');

        $path = $this->resolveFilePath($document);

        if (! $path || ! file_exists($path)) {
            return back()->with('error', "Document '{$document->original_filename}' could not be located on the server filesystem.");
        }

        $this->auditService->log(
            action: 'Document Downloaded',
            recordType: 'SupportingDocument',
            recordId: $document->id,
            description: "Admin downloaded document '{$document->original_filename}' for submission #{$document->due_diligence_submission_id}",
            user: $request->user()
        );

        return response()->download($path, $document->original_filename, [
            'Content-Type' => $document->mime_type ?: 'application/octet-stream',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }

    /**
     * Locate a supporting document file on server storage using multiple fallback strategies.
     */
    protected function resolveFilePath(SupportingDocument $document): ?string
    {
        $relativePath = $document->storage_path;

        if (empty($relativePath)) {
            return null;
        }

        // 1. Direct check in 'local' disk
        if (Storage::disk('local')->exists($relativePath)) {
            return Storage::disk('local')->path($relativePath);
        }

        // 2. Direct storage_path check under storage/app/
        $appPath = storage_path('app/'.$relativePath);
        if (file_exists($appPath)) {
            return $appPath;
        }

        // 3. Storage path with stripped 'private/' prefix under storage/app/private/
        $strippedPrivate = preg_replace('#^private/#', '', $relativePath);
        $privatePath = storage_path('app/private/'.$strippedPrivate);
        if (file_exists($privatePath)) {
            return $privatePath;
        }

        // 4. Storage path with stripped 'private/' prefix under storage/app/
        $appStrippedPath = storage_path('app/'.$strippedPrivate);
        if (file_exists($appStrippedPath)) {
            return $appStrippedPath;
        }

        // 5. Fallback in 'public' disk
        if (Storage::disk('public')->exists($relativePath)) {
            return Storage::disk('public')->path($relativePath);
        }

        return null;
    }
}
