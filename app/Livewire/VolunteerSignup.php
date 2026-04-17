<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Position;
use App\Models\Signup;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
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

        $existing = User::where('email', $this->email)->first();
        if ($existing && $existing->isAdmin()) {
            $this->addError('email', 'This email belongs to an admin account. Please log in instead.');
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
                'phone' => $this->phone,
                'role' => 'volunteer',
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
            ->with(['event.type', 'category', 'signups'])
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
