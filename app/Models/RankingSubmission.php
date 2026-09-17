<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RankingSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'exercise_id',
        'discipline_id',
        'status',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(RankingExercise::class, 'exercise_id');
    }

    public function discipline(): BelongsTo
    {
        return $this->belongsTo(Discipline::class);
    }

    public function rankings(): HasMany
    {
        return $this->hasMany(Ranking::class, 'submission_id')->orderBy('ranking_number');
    }
}
