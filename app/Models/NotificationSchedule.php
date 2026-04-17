<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationSchedule extends Model
{
    protected $fillable = ['event_id', 'label', 'offset_minutes'];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function isGlobal(): bool
    {
        return $this->event_id === null;
    }
}
