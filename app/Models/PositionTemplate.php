<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PositionTemplate extends Model
{
    protected $fillable = ['title', 'description', 'category_id', 'default_duration_minutes'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }
}
