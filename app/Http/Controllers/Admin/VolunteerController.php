<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\VolunteerApprovedMail;
use App\Models\Category;
use App\Models\Signup;
use App\Models\User;
use App\Support\SmsSender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class VolunteerController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $from = $request->input('from') ?: null; // date string or null
        $to = $request->input('to') ?: null;
        $status = $request->input('status') ?: null; // 'pending' | null

        $volunteers = $this->buildVolunteerQuery($q, $from, $to, $status)
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        // Grand total across the filtered range (not paginated — everybody's hours).
        $totalHours = $this->hoursSumQuery($from, $to)->value('total') ?? 0;
        $pendingCount = User::where('role', 'volunteer')->whereNull('approved_at')->count();

        return view('admin.volunteers.index', compact('volunteers', 'q', 'from', 'to', 'status', 'totalHours', 'pendingCount'));
    }

    public function exportCsv(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $from = $request->input('from') ?: null;
        $to = $request->input('to') ?: null;

        $volunteers = $this->buildVolunteerQuery($q, $from, $to)->orderBy('name')->get();
        $label = trim(($from ? 'from ' . $from : '') . ' ' . ($to ? 'through ' . $to : ''));
        $filename = 'volunteer-hours-' . ($from ?: 'all') . '-' . ($to ?: now()->toDateString()) . '.csv';

        $callback = function () use ($volunteers, $label) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Name', 'Email', 'Phone', 'Hours' . ($label ? " ($label)" : ' (lifetime)'), 'Attended events']);
            foreach ($volunteers as $v) {
                fputcsv($out, [
                    $v->name,
                    $v->email,
                    $v->phone,
                    number_format((float) ($v->hours_in_range ?? 0), 2),
                    $v->attended_count ?? 0,
                ]);
            }
            fclose($out);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function buildVolunteerQuery(string $q, ?string $from, ?string $to, ?string $status = null)
    {
        return User::query()
            // Include admins who also volunteer — otherwise their hours
            // are invisible on reports. Pure admins (no signups ever) are
            // still excluded to keep the list focused on the people the
            // hours report is about.
            ->where(function ($scope) {
                $scope->where('role', 'volunteer')
                    ->orWhereHas('signups');
            })
            ->when($status === 'pending', fn ($query) => $query->whereNull('approved_at')->where('role', 'volunteer'))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%");
                });
            })
            ->withCount(['signups as upcoming_signups_count' => function ($q) {
                $q->whereHas('position.event', fn ($e) => $e->where('starts_at', '>=', now()))
                  ->whereIn('status', ['confirmed', 'waitlisted']);
            }])
            ->withCount(['signups as attended_count' => function ($q) use ($from, $to) {
                $q->where('status', 'attended');
                if ($from) $q->whereHas('position.event', fn ($e) => $e->where('starts_at', '>=', $from));
                if ($to)   $q->whereHas('position.event', fn ($e) => $e->where('starts_at', '<=', $to . ' 23:59:59'));
            }])
            ->withSum(['signups as hours_in_range' => function ($q) use ($from, $to) {
                $q->where('status', 'attended');
                if ($from) $q->whereHas('position.event', fn ($e) => $e->where('starts_at', '>=', $from));
                if ($to)   $q->whereHas('position.event', fn ($e) => $e->where('starts_at', '<=', $to . ' 23:59:59'));
            }], 'hours_worked')
            ->with('categories');
    }

    private function hoursSumQuery(?string $from, ?string $to)
    {
        return Signup::query()
            ->where('status', 'attended')
            ->selectRaw('SUM(hours_worked) as total')
            ->when($from, fn ($q) => $q->whereHas('position.event', fn ($e) => $e->where('starts_at', '>=', $from)))
            ->when($to, fn ($q) => $q->whereHas('position.event', fn ($e) => $e->where('starts_at', '<=', $to . ' 23:59:59')));
    }

    public function show(User $volunteer)
    {
        abort_unless(
            $volunteer->role === 'volunteer' || $volunteer->signups()->exists(),
            404
        );

        $upcomingSignups = Signup::with(['position.event.template', 'position.category'])
            ->where('user_id', $volunteer->id)
            ->whereHas('position.event', fn ($q) => $q->where('starts_at', '>=', now()))
            ->get()
            ->sortBy(fn ($s) => $s->position->event->starts_at)
            ->values();

        $pastSignups = Signup::with(['position.event'])
            ->where('user_id', $volunteer->id)
            ->whereHas('position.event', fn ($q) => $q->where('starts_at', '<', now()))
            ->get()
            ->sortByDesc(fn ($s) => $s->position->event->starts_at)
            ->values();

        $totalHours = $pastSignups->where('status', 'attended')->sum('hours_worked');

        return view('admin.volunteers.show', [
            'volunteer' => $volunteer->loadMissing('categories'),
            'upcomingSignups' => $upcomingSignups,
            'pastSignups' => $pastSignups,
            'totalHours' => $totalHours,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(User $volunteer, Request $request)
    {
        abort_unless(
            $volunteer->role === 'volunteer' || $volunteer->signups()->exists(),
            404
        );

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($volunteer->id)],
            'phone' => 'nullable|string|max:30',
            'sms_opt_in' => 'nullable|boolean',
            'age_verified' => 'nullable|boolean',
            'background_check_verified' => 'nullable|boolean',
            'categories' => 'nullable|array',
            'categories.*' => 'integer|exists:categories,id',
        ]);

        $rawPhone = $data['phone'] ?? null;
        $e164 = $rawPhone ? SmsSender::toE164($rawPhone) : null;
        if ($rawPhone && ! $e164) {
            return back()->withErrors(['phone' => 'Phone must be a US number with 10 digits — e.g. (850) 555-1234.'])->withInput();
        }

        $wasPending = $volunteer->isPendingReview();

        $ageVerifiedAt = ($data['age_verified'] ?? false)
            ? ($volunteer->age_verified_at ?? now())
            : null;
        $bgVerifiedAt = ($data['background_check_verified'] ?? false)
            ? ($volunteer->background_check_verified_at ?? now())
            : null;

        $volunteer->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $e164 ?: $rawPhone,
            'sms_opt_in' => (bool) ($data['sms_opt_in'] ?? false),
            'age_verified_at' => $ageVerifiedAt,
            'background_check_verified_at' => $bgVerifiedAt,
        ]);

        // Auto-compute approved_at: user is approved if every cert they
        // triggered has a matching admin verification. No pending path
        // (user didn't trigger any cert) => they stay approved.
        $volunteer->approved_at = $volunteer->hasAllRequiredVerifications()
            ? ($volunteer->approved_at ?? now())
            : null;

        $volunteer->save();

        $volunteer->categories()->sync($data['categories'] ?? []);

        // Refresh to pick up signup-based verification checks — they read
        // relations that might have changed via a category resync.
        $volunteer->refresh();
        $volunteer->approved_at = $volunteer->hasAllRequiredVerifications()
            ? ($volunteer->approved_at ?? now())
            : null;
        $volunteer->save();

        $resolved = 0;
        $demoted = 0;
        if ($wasPending && $volunteer->isApproved()) {
            // pending -> approved: promote queued signups + email the
            // volunteer their magic link so they can pick shifts.
            $resolved = $this->resolvePendingSignups($volunteer);
            Mail::to($volunteer->email)->send(new VolunteerApprovedMail($volunteer));
        } elseif (! $wasPending && $volunteer->isPendingReview()) {
            // approved -> pending: demote committed signups so they hold
            // the slot but don't count as confirmed until re-verified.
            $demoted = $this->demoteActiveSignups($volunteer);
        }

        $message = "Saved {$volunteer->name}.";
        if ($resolved > 0) {
            $message .= " Promoted {$resolved} queued signup" . ($resolved === 1 ? '' : 's') . '.';
        }
        if ($demoted > 0) {
            $message .= " Demoted {$demoted} active signup" . ($demoted === 1 ? '' : 's') . ' to pending.';
        }

        return redirect()->route('admin.volunteers.show', $volunteer)->with('status', $message);
    }

    /**
     * Quick status toggle — bypasses the full edit form so admins can
     * flip a volunteer between approved and pending in one click.
     */
    public function setStatus(User $volunteer, Request $request)
    {
        abort_unless(
            $volunteer->role === 'volunteer' || $volunteer->signups()->exists(),
            404
        );

        $data = $request->validate([
            'status' => 'required|in:approved,pending',
        ]);

        $wasPending = $volunteer->isPendingReview();

        if ($data['status'] === 'approved') {
            $volunteer->approved_at = $volunteer->approved_at ?? now();
        } else {
            $volunteer->approved_at = null;
        }
        $volunteer->save();

        $note = '';
        if ($wasPending && $volunteer->isApproved()) {
            $n = $this->resolvePendingSignups($volunteer);
            Mail::to($volunteer->email)->send(new VolunteerApprovedMail($volunteer));
            if ($n > 0) $note = " Promoted {$n} queued signup" . ($n === 1 ? '' : 's') . '.';
        } elseif (! $wasPending && $volunteer->isPendingReview()) {
            $n = $this->demoteActiveSignups($volunteer);
            if ($n > 0) $note = " Demoted {$n} active signup" . ($n === 1 ? '' : 's') . ' to pending.';
        }

        $statusLabel = $volunteer->isApproved() ? 'Approved' : 'Pending review';
        return back()->with('status', "{$volunteer->name}: {$statusLabel}.{$note}");
    }

    private function resolvePendingSignups(User $volunteer): int
    {
        $pending = Signup::with('position.signups')
            ->where('user_id', $volunteer->id)
            ->where('status', 'pending')
            ->get();

        foreach ($pending as $signup) {
            $position = $signup->position;
            $confirmedCount = $position->signups
                ->where('status', 'confirmed')
                ->where('id', '!=', $signup->id)
                ->count();
            $signup->status = $confirmedCount < $position->slots_needed ? 'confirmed' : 'waitlisted';
            $signup->save();
        }

        return $pending->count();
    }

    /**
     * Demote confirmed/waitlisted signups back to 'pending' when the
     * volunteer flips approved -> pending. Leaves attended, cancelled,
     * and no_show alone since those are historical/explicit states.
     */
    private function demoteActiveSignups(User $volunteer): int
    {
        return Signup::where('user_id', $volunteer->id)
            ->whereIn('status', ['confirmed', 'waitlisted'])
            ->update(['status' => 'pending']);
    }

    public function create()
    {
        return view('admin.volunteers.create', [
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone' => 'nullable|string|max:30',
            'categories' => 'nullable|array',
            'categories.*' => 'integer|exists:categories,id',
            'age_certified' => 'nullable|boolean',
            'background_check_acknowledged' => 'nullable|boolean',
            'opportunity_alerts_opt_in' => 'nullable|boolean',
            'action' => 'nullable|in:pending,approve',
        ]);

        $rawPhone = $data['phone'] ?? null;
        $e164 = $rawPhone ? SmsSender::toE164($rawPhone) : null;
        if ($rawPhone && ! $e164) {
            return back()->withErrors(['phone' => 'Phone must be a US number with 10 digits — e.g. (850) 555-1234.'])->withInput();
        }

        $shouldApprove = ($data['action'] ?? 'pending') === 'approve';

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $e164 ?: $rawPhone,
            'role' => 'volunteer',
            'opportunity_alerts_opt_in' => (bool) ($data['opportunity_alerts_opt_in'] ?? true),
            'age_certified_at' => ($data['age_certified'] ?? false) ? now() : null,
            'age_certified_via' => ($data['age_certified'] ?? false) ? 'admin_intake' : null,
            'background_check_acknowledged_at' => ($data['background_check_acknowledged'] ?? false) ? now() : null,
            'background_check_acknowledged_via' => ($data['background_check_acknowledged'] ?? false) ? 'admin_intake' : null,
            'approved_at' => $shouldApprove ? now() : null,
        ]);

        if (! empty($data['categories'])) {
            $user->categories()->sync($data['categories']);
        }

        $statusMsg = "Added {$user->name}.";
        if ($shouldApprove) {
            Mail::to($user->email)->send(new VolunteerApprovedMail($user));
            $statusMsg .= ' Approval email sent.';
        } else {
            $statusMsg .= ' Pending your review.';
        }

        return redirect()->route('admin.volunteers.show', $user)->with('status', $statusMsg);
    }

    public function destroy(User $volunteer)
    {
        abort_unless($volunteer->role === 'volunteer', 404);
        $name = $volunteer->name;
        $volunteer->delete();

        return redirect()->route('admin.volunteers.index')
            ->with('status', "Deleted {$name}.");
    }
}
