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

#[Fillable(['name', 'email', 'phone', 'password', 'sms_opt_in',
    'opportunity_alerts_opt_in',
    'background_check_acknowledged_at', 'background_check_acknowledged_via',
    'age_certified_at', 'age_certified_via', 'approved_at',
    'background_check_verified_at', 'age_verified_at'])]
#[Hidden(['password', 'remember_token', 'totp_secret'])]
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
            'opportunity_alerts_opt_in' => 'boolean',
            'totp_secret' => 'encrypted',
            'totp_enabled_at' => 'datetime',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function hasTotpEnabled(): bool
    {
        return $this->totp_enabled_at !== null;
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
     * True when every certification the user has triggered has a matching
     * admin-verified timestamp. A certification is "triggered" by either:
     *   - the user acknowledging it during signup, OR
     *   - having an active signup that requires it (e.g. a Concessions
     *     position requires 18+; any position on a Kids Production event
     *     requires a background check).
     *
     * The signup-based check means revoking a verification on a volunteer
     * with committed shifts correctly drops them back to pending.
     */
    public function hasAllRequiredVerifications(): bool
    {
        if ($this->requiresBackgroundCheckVerification() && ! $this->background_check_verified_at) {
            return false;
        }
        if ($this->requiresAgeVerification() && ! $this->age_verified_at) {
            return false;
        }
        return true;
    }

    public static function attestationSourceLabel(?string $via): string
    {
        return match ($via) {
            'signup_form' => 'signup form',
            'admin_intake' => 'admin intake',
            default => '',
        };
    }

    public function requiresBackgroundCheckVerification(): bool
    {
        if ($this->background_check_acknowledged_at) return true;

        return $this->signups()
            ->whereIn('status', ['confirmed', 'waitlisted', 'pending', 'attended'])
            ->whereHas('position.event.template',
                fn ($q) => $q->where('requires_background_check', true))
            ->exists();
    }

    public function requiresAgeVerification(): bool
    {
        if ($this->age_certified_at) return true;

        return $this->signups()
            ->whereIn('status', ['confirmed', 'waitlisted', 'pending', 'attended'])
            ->whereHas('position.category',
                fn ($q) => $q->where('requires_age_certification', true))
            ->exists();
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
