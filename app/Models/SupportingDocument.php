<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportingDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'due_diligence_submission_id',
        'original_filename',
        'stored_filename',
        'storage_path',
        'mime_type',
        'file_size',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(DueDiligenceSubmission::class, 'due_diligence_submission_id');
    }
}
