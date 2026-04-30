<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Models\Signup;
use App\Support\OpportunityMatcher;
use App\Support\SmsSender;
use Illuminate\Http\Request;

class VolunteerDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if (! $user) {
            return redirect()->route('login-link');
        }

        if ($user->isAdmin()) {
            return redirect()->route('dashboard');
        }

        $upcomingSignups = Signup::with(['position.event.template', 'position.category'])
            ->where('user_id', $user->id)
            ->whereHas('position.event', fn ($q) => $q->where('starts_at', '>=', now()))
            ->whereIn('status', ['confirmed', 'waitlisted'])
            ->get()
            ->sortBy(fn ($s) => $s->position->event->starts_at)
            ->values();

        $pastSignups = Signup::with(['position.event'])
            ->where('user_id', $user->id)
            ->whereHas('position.event', fn ($q) => $q->where('starts_at', '<', now()))
            ->get()
            ->sortByDesc(fn ($s) => $s->position->event->starts_at)
            ->values();

        // Only approved volunteers see the opportunity browser. Pending users
        // get a "we're reviewing your application" callout instead.
        $opportunities = collect();
        if ($user->isApproved()) {
            $alreadySignedUpPositionIds = Signup::where('user_id', $user->id)
                ->whereNotIn('status', ['cancelled', 'no_show'])
                ->pluck('position_id')
                ->all();
            $opportunities = OpportunityMatcher::forUser($user)
                ->reject(fn ($p) => in_array($p->id, $alreadySignedUpPositionIds, true))
                ->values();
        }

        return view('volunteer.dashboard', [
            'user' => $user->loadMissing('categories'),
            'upcomingSignups' => $upcomingSignups,
            'pastSignups' => $pastSignups,
            'opportunities' => $opportunities,
        ]);
    }

    public function signUp(Request $request)
    {
        $user = $request->user();
        abort_unless($user && ! $user->isAdmin(), 403);
        abort_unless($user->isApproved(), 403, 'Your account is pending review.');

        $data = $request->validate([
            'position_id' => 'required|integer|exists:positions,id',
        ]);

        $position = Position::with('signups')->findOrFail($data['position_id']);
        abort_unless($position->is_public, 403);

        $existing = Signup::where('user_id', $user->id)
            ->where('position_id', $position->id)
            ->first();
        if ($existing) {
            return redirect()->route('volunteer.dashboard')
                ->with('status', "You're already signed up for {$position->title}.");
        }

        $status = $position->isFull() ? 'waitlisted' : 'confirmed';

        Signup::create([
            'user_id' => $user->id,
            'position_id' => $position->id,
            'status' => $status,
        ]);

        $label = $status === 'waitlisted' ? 'Added to the waitlist for' : 'Signed up for';
        return redirect()->route('volunteer.dashboard')
            ->with('status', "{$label} {$position->title} at {$position->event->title}.");
    }

    public function updatePreferences(Request $request)
    {
        $user = $request->user();
        abort_unless($user && ! $user->isAdmin(), 403);

        $data = $request->validate([
            'phone' => 'nullable|string|max:30',
            'sms_opt_in' => 'sometimes|boolean',
            'opportunity_alerts_opt_in' => 'sometimes|boolean',
        ]);

        $smsOptIn = (bool) ($data['sms_opt_in'] ?? false);
        $rawPhone = $data['phone'] ?? null;
        $e164 = $rawPhone ? SmsSender::toE164($rawPhone) : null;

        if ($rawPhone && ! $e164) {
            return back()->withErrors(['phone' => 'Phone must be a US number with 10 digits — e.g. (850) 555-1234.']);
        }
        if ($smsOptIn && ! $e164) {
            return back()->withErrors(['phone' => 'A valid phone number is required to receive text reminders.']);
        }

        $wasOptedIn = (bool) $user->sms_opt_in;

        $user->update([
            'phone' => $e164 ?: $rawPhone,
            'sms_opt_in' => $smsOptIn,
            'opportunity_alerts_opt_in' => (bool) ($data['opportunity_alerts_opt_in'] ?? false),
        ]);

        if (! $wasOptedIn && $smsOptIn && $e164) {
            SmsSender::fromConfig()->send($e164, SmsSender::OPT_IN_CONFIRMATION_BODY);
        }

        return redirect()->route('volunteer.dashboard')
            ->with('status', 'Preferences updated.');
    }
}
