<?php

namespace App\Models;

use App\Support\DurationFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationSchedule extends Model
{
    protected $fillable = ['event_id', 'label', 'offset_minutes'];

    protected static function booted(): void
    {
        static::saving(function (NotificationSchedule $schedule) {
            $schedule->label = DurationFormatter::beforeEvent((int) $schedule->offset_minutes);
        });
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function isGlobal(): bool
    {
        return $this->event_id === null;
    }
}
