<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DueDiligenceCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
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

    public function submissions(): HasMany
    {
        return $this->hasMany(DueDiligenceSubmission::class, 'category_id');
    }
}
