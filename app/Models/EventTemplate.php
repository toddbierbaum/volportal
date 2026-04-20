<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventTemplate extends Model
{
    protected $fillable = ['name', 'slug', 'color', 'description', 'requires_background_check'];

    protected function casts(): array
    {
        return [
            'requires_background_check' => 'boolean',
        ];
    }

    public function positions(): HasMany
    {
        return $this->hasMany(EventTemplatePosition::class)->orderBy('position_order');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(EventTemplateSchedule::class)->orderByDesc('offset_minutes');
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }
}
