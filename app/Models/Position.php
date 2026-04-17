<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    protected $fillable = [
        'event_id',
        'category_id',
        'position_template_id',
        'title',
        'description',
        'slots_needed',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(PositionTemplate::class, 'position_template_id');
    }

    public function signups(): HasMany
    {
        return $this->hasMany(Signup::class);
    }

    public function confirmedSignups(): HasMany
    {
        return $this->signups()->where('status', 'confirmed');
    }

    public function waitlistedSignups(): HasMany
    {
        return $this->signups()->where('status', 'waitlisted')->orderBy('created_at');
    }

    public function slotsRemaining(): int
    {
        return max(0, $this->slots_needed - $this->confirmedSignups()->count());
    }

    public function isFull(): bool
    {
        return $this->slotsRemaining() === 0;
    }
}
