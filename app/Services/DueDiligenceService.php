<?php

namespace App\Services;

use App\Models\Candidate;
use App\Models\DueDiligenceCategory;
use App\Models\DueDiligenceSubmission;
use App\Models\SupportingDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DueDiligenceService
{
    /**
     * Allowed file extensions.
     *
     * @var list<string>
     */
    public const ALLOWED_EXTENSIONS = [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png',
    ];

    public function __construct(
        protected AuditService $auditService
    ) {}

    /**
     * Get active due diligence categories.
     */
    public function getCategories(): Collection
    {
        return DueDiligenceCategory::where('active', true)
            ->orderBy('display_order')
            ->get();
    }

    /**
     * Submit a due diligence record with optional supporting files.
     *
     * @param  list<UploadedFile>  $files
     * @param  array<string, mixed>  $referenceData
     */
    public function submit(
        User $user,
        int $candidateId,
        int $categoryId,
        string $comment,
        array $files = [],
        array $referenceData = []
    ): DueDiligenceSubmission {
        $candidate = Candidate::findOrFail($candidateId);
        $category = DueDiligenceCategory::findOrFail($categoryId);

        return DB::transaction(function () use ($user, $candidate, $category, $comment, $files, $referenceData) {
            $submissionData = array_merge([
                'user_id' => $user->id,
                'candidate_id' => $candidate->id,
                'category_id' => $category->id,
                'comment' => trim($comment),
                'user_discipline_id' => $user->discipline_id,
            ], array_intersect_key($referenceData, array_flip([
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
            ])));

            $submission = DueDiligenceSubmission::create($submissionData);

            foreach ($files as $file) {
                if (! $file instanceof UploadedFile) {
                    continue;
                }

                $extension = strtolower($file->getClientOriginalExtension());
                if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
                    throw ValidationException::withMessages([
                        'documents' => "File extension '{$extension}' is not allowed for security reasons.",
                    ]);
                }

                $originalName = $file->getClientOriginalName();
                $uuidName = Str::uuid()->toString().'.'.$extension;
                $storageDir = 'private/due_diligence/'.$submission->id;
                $storedPath = $file->storeAs($storageDir, $uuidName, 'local');

                SupportingDocument::create([
                    'due_diligence_submission_id' => $submission->id,
                    'original_filename' => $originalName,
                    'stored_filename' => $uuidName,
                    'storage_path' => $storedPath,
                    'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
                    'file_size' => $file->getSize(),
                ]);

                $this->auditService->log(
                    action: 'File Uploaded',
                    recordType: 'SupportingDocument',
                    recordId: $submission->id,
                    description: "Uploaded document: {$originalName}",
                    user: $user
                );
            }

            $this->auditService->log(
                action: 'Due Diligence Submitted',
                recordType: 'DueDiligenceSubmission',
                recordId: $submission->id,
                description: "Submitted due diligence for candidate #{$candidate->id} ({$candidate->candidate_name}) under {$category->name}",
                user: $user
            );

            return $submission;
        });
    }

    /**
     * Delete an attached supporting document.
     */
    public function deleteDocument(User $user, SupportingDocument $document): bool
    {
        if (Storage::disk('local')->exists($document->storage_path)) {
            Storage::disk('local')->delete($document->storage_path);
        }

        $documentId = $document->id;
        $filename = $document->original_filename;
        $document->delete();

        $this->auditService->log(
            action: 'File Deleted',
            recordType: 'SupportingDocument',
            recordId: $documentId,
            description: "Deleted file: {$filename}",
            user: $user
        );

        return true;
    }
}
