<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'discipline_id',
        'active',
        'last_login',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
            'last_login' => 'datetime',
        ];
    }

    public function isVotingUser(): bool
    {
        return $this->role === 'voting_user';
    }

    public function isReviewer(): bool
    {
        return $this->role === 'reviewer';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'administrator';
    }

    public function hasDiscipline(): bool
    {
        return ! is_null($this->discipline_id);
    }

    public function discipline(): BelongsTo
    {
        return $this->belongsTo(Discipline::class);
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
        return $this->hasMany(DueDiligenceSubmission::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }
}
