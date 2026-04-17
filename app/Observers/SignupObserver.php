<?php

namespace App\Observers;

use App\Models\Position;
use App\Models\Signup;

class SignupObserver
{
    public function updated(Signup $signup): void
    {
        if (! $signup->wasChanged('status')) return;

        $original = $signup->getOriginal('status');
        $current = $signup->status;

        if ($original === 'confirmed' && in_array($current, ['cancelled', 'no_show'])) {
            $this->promoteWaitlist($signup->position_id);
        }
    }

    public function deleted(Signup $signup): void
    {
        if ($signup->status === 'confirmed') {
            $this->promoteWaitlist($signup->position_id);
        }
    }

    private function promoteWaitlist(int $positionId): void
    {
        $position = Position::with(['signups' => fn ($q) => $q->whereIn('status', ['confirmed', 'waitlisted'])])
            ->find($positionId);

        if (! $position) return;

        $confirmedCount = $position->signups->where('status', 'confirmed')->count();
        $openSlots = max(0, $position->slots_needed - $confirmedCount);

        if ($openSlots === 0) return;

        $waitlisted = $position->signups
            ->where('status', 'waitlisted')
            ->sortBy('created_at')
            ->take($openSlots);

        foreach ($waitlisted as $s) {
            $s->updateQuietly(['status' => 'confirmed']);
        }
    }
}
