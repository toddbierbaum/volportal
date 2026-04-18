<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Signup;
use App\Models\User;
use App\Support\SmsSender;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VolunteerController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->input('q', ''));

        $volunteers = User::query()
            ->where('role', 'volunteer')
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
            ->with('categories')
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        return view('admin.volunteers.index', compact('volunteers', 'q'));
    }

    public function show(User $volunteer)
    {
        abort_unless($volunteer->role === 'volunteer', 404);

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
        ]);
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
        ]);

        $rawPhone = $data['phone'] ?? null;
        $e164 = $rawPhone ? SmsSender::toE164($rawPhone) : null;
        if ($rawPhone && ! $e164) {
            return back()->withErrors(['phone' => 'Phone must be a US number with 10 digits — e.g. (850) 555-1234.'])->withInput();
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $e164 ?: $rawPhone,
            'role' => 'volunteer',
        ]);

        if (! empty($data['categories'])) {
            $user->categories()->sync($data['categories']);
        }

        return redirect()->route('admin.volunteers.show', $user)
            ->with('status', "Added {$user->name}.");
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
