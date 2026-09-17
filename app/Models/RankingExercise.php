<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RankingExercise extends Model
{
    use HasFactory;

    protected $fillable = [
        'exercise_name',
        'description',
        'start_datetime',
        'end_datetime',
        'status',
        'allow_resubmission',
    ];

    protected function casts(): array
    {
        return [
            'start_datetime' => 'datetime',
            'end_datetime' => 'datetime',
            'allow_resubmission' => 'boolean',
        ];
    }

    public function getTitleAttribute(): string
    {
        return $this->exercise_name ?? '';
    }

    public function getStartDateAttribute()
    {
        return $this->start_datetime;
    }

    public function getEndDateAttribute()
    {
        return $this->end_datetime;
    }

    public function isOpen(): bool
    {
        return $this->status === 'Open';
    }

    public function isWithinTimeWindow(): bool
    {
        $now = now();
        if ($this->start_datetime && $now->lt($this->start_datetime)) {
            return false;
        }
        if ($this->end_datetime && $now->gt($this->end_datetime)) {
            return false;
        }

        return true;
    }

    public function canAcceptSubmissions(): bool
    {
        return $this->isOpen() && $this->isWithinTimeWindow();
    }

    public function rankingSubmissions(): HasMany
    {
        return $this->hasMany(RankingSubmission::class, 'exercise_id');
    }

    public function rankings(): HasMany
    {
        return $this->hasMany(Ranking::class, 'exercise_id');
    }
}
