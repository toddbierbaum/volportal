<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'phone', 'role', 'password', 'sms_opt_in',
    'background_check_acknowledged_at', 'age_certified_at', 'approved_at',
    'background_check_verified_at', 'age_verified_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'background_check_acknowledged_at' => 'datetime',
            'background_check_verified_at' => 'datetime',
            'age_certified_at' => 'datetime',
            'age_verified_at' => 'datetime',
            'approved_at' => 'datetime',
            'password' => 'hashed',
            'sms_opt_in' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isApproved(): bool
    {
        return $this->approved_at !== null;
    }

    public function isPendingReview(): bool
    {
        return $this->approved_at === null;
    }

    /**
     * True when every certification the user triggered has a matching
     * admin-verified timestamp. Users who triggered nothing are always
     * "fully verified" from this method's perspective.
     */
    public function hasAllRequiredVerifications(): bool
    {
        if ($this->background_check_acknowledged_at && ! $this->background_check_verified_at) {
            return false;
        }
        if ($this->age_certified_at && ! $this->age_verified_at) {
            return false;
        }
        return true;
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function signups(): HasMany
    {
        return $this->hasMany(Signup::class);
    }
}
