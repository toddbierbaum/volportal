<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventTemplatePosition extends Model
{
    protected $fillable = [
        'event_template_id',
        'category_id',
        'title',
        'description',
        'slots_needed',
        'is_public',
        'call_offset_minutes',
        'duration_minutes',
        'position_order',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EventTemplate::class, 'event_template_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
