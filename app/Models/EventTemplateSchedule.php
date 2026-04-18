<?php

namespace App\Models;

use App\Support\DurationFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventTemplateSchedule extends Model
{
    protected $fillable = ['event_template_id', 'offset_minutes', 'channel', 'label'];

    protected static function booted(): void
    {
        static::saving(function (EventTemplateSchedule $schedule) {
            $schedule->label = DurationFormatter::beforeEvent((int) $schedule->offset_minutes);
        });
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EventTemplate::class, 'event_template_id');
    }
}
