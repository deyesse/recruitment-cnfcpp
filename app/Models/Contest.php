<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contest extends Model
{
    protected $guarded = [
    ];

    protected function casts(): array
    {
        return [
            'ends_at'   => 'datetime',
            'starts_at' => 'datetime',
            'degrees'   => 'array',
            'show_score' => 'boolean',
            'is_test_mode' => 'boolean',
        ];
    }

    public function contestType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ContestType::class);
    }

    public function positions(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Position::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }
}
