<?php

namespace App\Livewire;

use App\Mail\MagicLinkMail;
use App\Mail\SignupConfirmationMail;
use App\Models\Category;
use App\Models\Position;
use App\Models\Signup;
use App\Models\User;
use App\Support\SmsSender;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Validate;
use Livewire\Component;

class VolunteerSignup extends Component
{
    public int $step = 1;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|email|max:255')]
    public string $email = '';

    #[Validate('required|string|max:30')]
    public string $phone = '';

    public bool $smsOptIn = false;

    /** @var array<int> */
    public array $selectedCategoryIds = [];

    public ?int $userId = null;

    /** @var array<int> signup IDs created during this session */
    public array $createdSignupIds = [];

    public function proceedToCategories(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:30',
        ]);

        if (! SmsSender::toE164($this->phone)) {
            $this->addError('phone', 'Phone must be a US number with 10 digits — e.g. (850) 555-1234.');
            return;
        }

        // Rate limit per IP so this form can't be used to enumerate
        // accounts or flood inboxes with magic-link emails.
        $key = 'signup:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('email', 'Too many requests. Please try again in a minute.');
            return;
        }
        RateLimiter::hit($key, 60);

        // Don't reveal whether an email belongs to an admin — just send
        // the magic link silently for any existing account. MagicLinkController
        // will block admins on redeem.
        $existing = User::where('email', $this->email)->first();
        if ($existing) {
            if (! $existing->isAdmin()) {
                Mail::to($existing->email)->send(new MagicLinkMail($existing));
            }
            $this->userId = $existing->id;
            $this->step = 5;
            return;
        }

        $this->step = 2;
    }

    public function backToDetails(): void
    {
        $this->step = 1;
    }

    public function proceedToMatches(): void
    {
        $this->validate([
            'selectedCategoryIds' => 'required|array|min:1',
            'selectedCategoryIds.*' => 'integer|exists:categories,id',
        ], [
            'selectedCategoryIds.required' => 'Pick at least one category you are interested in.',
            'selectedCategoryIds.min' => 'Pick at least one category you are interested in.',
        ]);

        $user = User::updateOrCreate(
            ['email' => $this->email],
            [
                'name' => $this->name,
                'phone' => SmsSender::toE164($this->phone) ?? $this->phone,
                'role' => 'volunteer',
                'sms_opt_in' => $this->smsOptIn,
            ]
        );

        $user->categories()->syncWithoutDetaching($this->selectedCategoryIds);

        $this->userId = $user->id;
        $this->step = 3;
    }

    public function signUp(int $positionId): void
    {
        if (! $this->userId) {
            $this->addError('signup', 'Please start from the beginning.');
            return;
        }

        $position = Position::with('signups')->findOrFail($positionId);

        $existing = Signup::where('user_id', $this->userId)
            ->where('position_id', $positionId)
            ->first();

        if ($existing) {
            return;
        }

        $status = $position->isFull() ? 'waitlisted' : 'confirmed';

        $signup = Signup::create([
            'user_id' => $this->userId,
            'position_id' => $positionId,
            'status' => $status,
        ]);

        $this->createdSignupIds[] = $signup->id;
    }

    public function finish(): void
    {
        if ($this->userId) {
            $user = User::find($this->userId);
            $signups = Signup::with(['position.event'])
                ->whereIn('id', $this->createdSignupIds)
                ->get();

            if ($user) {
                Mail::to($user->email)->send(new SignupConfirmationMail($user, $signups));
                Cookie::queue(cookie()->forever('volunteer_id', (string) $user->id));
            }
        }

        $this->step = 4;
    }

    public function getCategoriesProperty(): Collection
    {
        return Category::orderBy('name')->get();
    }

    public function getMatchedPositionsProperty(): Collection
    {
        if (empty($this->selectedCategoryIds)) {
            return collect();
        }

        return Position::query()
            ->with(['event.template', 'category', 'signups'])
            ->where('is_public', true)
            ->whereIn('category_id', $this->selectedCategoryIds)
            ->whereHas('event', fn ($q) => $q
                ->where('is_published', true)
                ->where('starts_at', '>=', now()))
            ->get()
            ->sortBy(fn ($p) => $p->event->starts_at)
            ->values();
    }

    public function getCreatedSignupsProperty(): Collection
    {
        if (empty($this->createdSignupIds)) {
            return collect();
        }

        return Signup::with(['position.event', 'position.category'])
            ->whereIn('id', $this->createdSignupIds)
            ->get();
    }

    public function render()
    {
        return view('livewire.volunteer-signup', [
            'categories' => $this->categories,
            'matchedPositions' => $this->matchedPositions,
            'createdSignups' => $this->createdSignups,
        ]);
    }
}
