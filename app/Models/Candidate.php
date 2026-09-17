<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Candidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'discipline_id',
        'candidate_name',
        'candidate_title',
        'organisation',
        'photo_url',
        'affiliation_to_asm',
        'basis_of_recommendation',
        'area_of_expertise',
        'qualifications',
        'professional_memberships',
        'qualifications_professional_memberships',
        'short_description',
        'nomination_form_url',
        'display_order',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'display_order' => 'integer',
        ];
    }

    public function getPhotoPathAttribute(): ?string
    {
        return $this->photo_url;
    }

    public function getPhotoAttribute(): ?string
    {
        return $this->photo_url;
    }

    public function getFullNameAttribute(): string
    {
        return $this->candidate_name ?? '';
    }

    public function setFullNameAttribute($value): void
    {
        $this->attributes['candidate_name'] = $value;
    }

    public function getTitleDesignationAttribute(): ?string
    {
        return $this->candidate_title;
    }

    public function setTitleDesignationAttribute($value): void
    {
        $this->attributes['candidate_title'] = $value;
    }

    public function getNominatedDisciplineIdAttribute(): ?int
    {
        return $this->discipline_id;
    }

    public function setNominatedDisciplineIdAttribute($value): void
    {
        $this->attributes['discipline_id'] = $value;
    }

    public function getOnedriveDossierLinkAttribute(): ?string
    {
        return $this->nomination_form_url;
    }

    public function setOnedriveDossierLinkAttribute($value): void
    {
        $this->attributes['nomination_form_url'] = $value;
    }

    public function getOnedriveLinkAttribute(): ?string
    {
        return $this->nomination_form_url;
    }

    public function getAreasOfExpertiseAttribute(): ?string
    {
        return $this->area_of_expertise;
    }

    public function setAreasOfExpertiseAttribute($value): void
    {
        $this->attributes['area_of_expertise'] = $value;
    }

    public function getSubDisciplineAttribute(): ?string
    {
        return $this->area_of_expertise;
    }

    public function getQualificationsProfessionalMembershipsAttribute(): ?string
    {
        if (! empty($this->attributes['qualifications_professional_memberships'])) {
            return $this->attributes['qualifications_professional_memberships'];
        }

        $parts = array_filter([$this->qualifications, $this->professional_memberships]);

        return ! empty($parts) ? implode("\n", $parts) : null;
    }

    public function setQualificationsProfessionalMembershipsAttribute($value): void
    {
        $this->attributes['qualifications_professional_memberships'] = $value;
        if (empty($this->attributes['qualifications'])) {
            $this->attributes['qualifications'] = $value;
        }
    }

    public function getProfileSummaryAttribute(): ?string
    {
        return $this->short_description;
    }

    public function getKeyAchievementsAttribute(): ?string
    {
        return $this->basis_of_recommendation;
    }

    public function getHighestQualificationAttribute(): ?string
    {
        return $this->qualifications;
    }

    public function discipline(): BelongsTo
    {
        return $this->belongsTo(Discipline::class);
    }

    public function rankings(): HasMany
    {
        return $this->hasMany(Ranking::class);
    }

    public function dueDiligenceSubmissions(): HasMany
    {
        return $this->hasMany(DueDiligenceSubmission::class);
    }
}
