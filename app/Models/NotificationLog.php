<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    protected $fillable = ['signup_id', 'notification_schedule_id', 'offset_minutes', 'type', 'sent_at'];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function signup(): BelongsTo
    {
        return $this->belongsTo(Signup::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(NotificationSchedule::class, 'notification_schedule_id');
    }
}
