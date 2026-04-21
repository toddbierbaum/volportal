<?php

namespace App\Livewire;

use App\Mail\MagicLinkMail;
use App\Mail\SignupConfirmationMail;
use App\Models\Category;
use App\Models\Event;
use App\Models\Position;
use App\Models\Setting;
use App\Models\Signup;
use App\Models\User;
use App\Support\EmailSendThrottle;
use App\Support\OpportunityMatcher;
use App\Support\SmsSender;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Mail;
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

    // Honeypot — hidden field, legit users never fill it.
    public string $website = '';

    public bool $smsOptIn = false;

    public bool $opportunityAlertsOptIn = true;

    /** @var array<int> */
    public array $selectedCategoryIds = [];

    public ?int $userId = null;

    /** @var array<int> signup IDs created during this session */
    public array $createdSignupIds = [];

    // Certification acknowledgments within the wizard session.
    // Persisted to User timestamps when we create/update the user record.
    public bool $backgroundCheckAcknowledged = false;
    public bool $ageCertified = false;

    public function proceedToCategories(): void
    {
        // Honeypot trip — pretend it worked so scrapers can't tell they
        // were detected. Same branch as a successful existing-user send.
        if ($this->website !== '') {
            $this->step = 5;
            return;
        }

        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:30',
        ]);

        if (! SmsSender::toE164($this->phone)) {
            $this->addError('phone', 'Phone must be a US number with 10 digits — e.g. (850) 555-1234.');
            return;
        }

        if (! EmailSendThrottle::allow($this->email, request()->ip())) {
            $this->addError('email', 'Too many requests. Please wait an hour before trying again.');
            return;
        }

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

        // Route through certification screens first if any picked category
        // requires them. User record isn't created until all acknowledgments
        // are collected, so there's no half-complete row if they bail.
        if ($this->needsBackgroundCheck() && ! $this->backgroundCheckAcknowledged) {
            $this->step = 6;
            return;
        }
        if ($this->needsAgeCertification() && ! $this->ageCertified) {
            $this->step = 7;
            return;
        }

        $this->persistUser();
        $this->advanceAfterPersist();
    }

    public function acknowledgeBackgroundCheck(): void
    {
        $this->backgroundCheckAcknowledged = true;
        // Still need age cert? Go there next. Otherwise create user + matches.
        if ($this->needsAgeCertification() && ! $this->ageCertified) {
            $this->step = 7;
            return;
        }
        $this->persistUser();
        $this->advanceAfterPersist();
    }

    public function certifyAge(): void
    {
        $this->ageCertified = true;
        $this->persistUser();
        $this->advanceAfterPersist();
    }

    private function advanceAfterPersist(): void
    {
        // Gate is ON → new volunteers wait for admin approval before seeing
        // shifts. Show step 8 (pending-review confirmation) instead of step 3.
        $this->step = $this->requiresApprovalGate() ? 8 : 3;
    }

    public function requiresApprovalGate(): bool
    {
        return (bool) Setting::get('require_approval_before_opportunities', false);
    }

    private function persistUser(): void
    {
        // When the approval gate is on, everyone lands in pending — regardless
        // of whether they triggered a cert screen.
        $requiresReview = $this->backgroundCheckAcknowledged
            || $this->ageCertified
            || $this->requiresApprovalGate();

        $user = User::updateOrCreate(
            ['email' => $this->email],
            [
                'name' => $this->name,
                'phone' => SmsSender::toE164($this->phone) ?? $this->phone,
                'role' => 'volunteer',
                'sms_opt_in' => $this->smsOptIn,
                'opportunity_alerts_opt_in' => $this->opportunityAlertsOptIn,
                'background_check_acknowledged_at' => $this->backgroundCheckAcknowledged ? now() : null,
                'background_check_acknowledged_via' => $this->backgroundCheckAcknowledged ? 'signup_form' : null,
                'age_certified_at' => $this->ageCertified ? now() : null,
                'age_certified_via' => $this->ageCertified ? 'signup_form' : null,
                'approved_at' => $requiresReview ? null : now(),
            ]
        );

        $user->categories()->syncWithoutDetaching($this->selectedCategoryIds);

        $this->userId = $user->id;
    }

    /**
     * Progress-bar steps. Dynamic because the cert screens only appear
     * when a picked category triggers them; we include 'em in the indicator
     * as soon as we know they apply OR have already been acknowledged.
     *
     * Each entry: ['label' => 'Your info', 'steps' => [1]] — 'steps' are
     * the $this->step values that map to that progress position.
     *
     * @return array<int, array{label: string, steps: array<int>}>
     */
    public function getProgressStepsProperty(): array
    {
        $out = [
            ['label' => 'Your info',   'steps' => [1]],
            ['label' => 'Interests',   'steps' => [2]],
        ];

        if ($this->backgroundCheckAcknowledged || $this->needsBackgroundCheck()) {
            $out[] = ['label' => 'Background check', 'steps' => [6]];
        }
        if ($this->ageCertified || $this->needsAgeCertification()) {
            $out[] = ['label' => 'Age 18+', 'steps' => [7]];
        }

        if ($this->requiresApprovalGate()) {
            $out[] = ['label' => 'Pending review', 'steps' => [8]];
        } else {
            $out[] = ['label' => 'Opportunities', 'steps' => [3]];
        }
        $out[] = ['label' => 'Done', 'steps' => [4, 5]];

        return $out;
    }

    public function getCurrentProgressIndexProperty(): int
    {
        foreach ($this->progressSteps as $i => $s) {
            if (in_array($this->step, $s['steps'], true)) {
                return $i;
            }
        }
        return 0;
    }

    public function needsBackgroundCheck(): bool
    {
        // BG check is tied to the event template. Trigger if either:
        // (1) any upcoming event on a BG-template has a public position
        //     matching the user's category interests (indirect — they'd
        //     probably pick up that shift), or
        // (2) any picked category is directly linked to a BG-template
        //     (direct — their interest in that event type implies it).
        if (empty($this->selectedCategoryIds)) return false;

        $templateLinkedTrigger = Category::whereIn('id', $this->selectedCategoryIds)
            ->whereHas('eventTemplate', fn ($q) => $q->where('requires_background_check', true))
            ->exists();

        if ($templateLinkedTrigger) return true;

        return Event::query()
            ->where('is_published', true)
            ->where('starts_at', '>=', now())
            ->whereHas('template', fn ($q) => $q->where('requires_background_check', true))
            ->whereHas('positions', fn ($q) => $q
                ->where('is_public', true)
                ->whereIn('category_id', $this->selectedCategoryIds))
            ->exists();
    }

    public function needsAgeCertification(): bool
    {
        if (empty($this->selectedCategoryIds)) return false;
        return Category::whereIn('id', $this->selectedCategoryIds)
            ->where('requires_age_certification', true)
            ->exists();
    }

    public function signUp(int $positionId): void
    {
        if (! $this->userId) {
            $this->addError('signup', 'Please start from the beginning.');
            return;
        }

        $position = Position::with('signups')->findOrFail($positionId);
        $user = User::find($this->userId);

        $existing = Signup::where('user_id', $this->userId)
            ->where('position_id', $positionId)
            ->first();

        if ($existing) {
            return;
        }

        // Pending users' signups are queued — they don't count against
        // position capacity and won't be scheduled until an admin approves
        // the user (which re-resolves pending signups to confirmed/waitlisted
        // based on current capacity at that moment).
        if ($user && $user->isPendingReview()) {
            $status = 'pending';
        } else {
            $status = $position->isFull() ? 'waitlisted' : 'confirmed';
        }

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
        return OpportunityMatcher::forCategoryIds($this->selectedCategoryIds);
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
            'progressSteps' => $this->progressSteps,
            'currentProgressIndex' => $this->currentProgressIndex,
        ]);
    }
}
