<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'color',
        'event_template_id', 'requires_age_certification',
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

    /**
     * Optional link to an event template. Picking this category as an
     * interest means "I want to work events of this template" — matching
     * expands to every position on events of the linked template, and
     * BG-check triggers if the template requires it.
     */
    public function eventTemplate(): BelongsTo
    {
        return $this->belongsTo(EventTemplate::class);
    }
}
