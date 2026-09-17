<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Discipline extends Model
{
    use HasFactory;

    protected $fillable = [
        'discipline_name',
        'description',
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

    public function getNameAttribute(): string
    {
        return $this->discipline_name ?? '';
    }

    public function getCodeAttribute(): string
    {
        if (preg_match('/^([A-Z]+)\s*-/', $this->discipline_name, $matches)) {
            return $matches[1];
        }

        return 'DISC-'.str_pad((string) $this->id, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Determine if candidate ranking is required for this discipline.
     * Single-candidate disciplines (count == 1) do NOT require ranking.
     */
    public function isRankingRequired(): bool
    {
        return $this->candidates()->where('active', true)->count() > 1;
    }

    /**
     * Check if this discipline has exactly one active candidate.
     */
    public function isSingleCandidate(): bool
    {
        return $this->candidates()->where('active', true)->count() === 1;
    }

    /**
     * Get the count of active candidates in this discipline.
     */
    public function getActiveCandidateCountAttribute(): int
    {
        return $this->candidates()->where('active', true)->count();
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class)->orderBy('display_order');
    }

    public function rankingSubmissions(): HasMany
    {
        return $this->hasMany(RankingSubmission::class);
    }

    public function rankings(): HasMany
    {
        return $this->hasMany(Ranking::class);
    }

    public function dueDiligenceSubmissions(): HasMany
    {
        return $this->hasMany(DueDiligenceSubmission::class, 'user_discipline_id');
    }
}
