<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ranking extends Model
{
    use HasFactory;

    protected $fillable = [
        'submission_id',
        'user_id',
        'exercise_id',
        'discipline_id',
        'candidate_id',
        'ranking_number',
    ];

    protected function casts(): array
    {
        return [
            'ranking_number' => 'integer',
        ];
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(RankingSubmission::class, 'submission_id');
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

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }
}
