<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    protected $fillable = ['title', 'slug', 'description', 'starts_at', 'ends_at', 'location', 'is_published'];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_published' => 'boolean',
        ];
    }

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }

    public function notificationSchedules(): HasMany
    {
        return $this->hasMany(NotificationSchedule::class);
    }
}
