<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'color',
        'requires_age_certification',
    ];

    protected function casts(): array
    {
        return [
            'requires_age_certification' => 'boolean',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function eventTemplatePositions(): HasMany
    {
        return $this->hasMany(EventTemplatePosition::class);
    }

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }
}
