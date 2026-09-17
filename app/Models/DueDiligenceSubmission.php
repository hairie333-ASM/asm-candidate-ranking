<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DueDiligenceSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'candidate_id',
        'category_id',
        'comment',
        'user_discipline_id',
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
    ];

    /**
     * Accessor for comment alias ($submission->comments).
     */
    public function getCommentsAttribute(): ?string
    {
        return $this->comment;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DueDiligenceCategory::class, 'category_id');
    }

    public function userDiscipline(): BelongsTo
    {
        return $this->belongsTo(Discipline::class, 'user_discipline_id');
    }

    public function supportingDocuments(): HasMany
    {
        return $this->hasMany(SupportingDocument::class, 'due_diligence_submission_id');
    }
}
