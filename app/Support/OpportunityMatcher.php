<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Position;
use App\Models\User;
use Illuminate\Support\Collection;

class OpportunityMatcher
{
    /**
     * Public positions on upcoming published events matching any of the given
     * category ids, OR on events whose template is linked to one of those
     * categories. Mirrors the volunteer-signup wizard's matching logic.
     *
     * @param  array<int>  $categoryIds
     */
    public static function forCategoryIds(array $categoryIds): Collection
    {
        if (empty($categoryIds)) return collect();

        $templateIds = Category::whereIn('id', $categoryIds)
            ->whereNotNull('event_template_id')
            ->pluck('event_template_id')
            ->all();

        return Position::query()
            ->with(['event.template', 'category', 'signups'])
            ->where('is_public', true)
            ->where(function ($q) use ($categoryIds, $templateIds) {
                $q->whereIn('category_id', $categoryIds);
                if (! empty($templateIds)) {
                    $q->orWhereHas('event', fn ($e) => $e->whereIn('event_template_id', $templateIds));
                }
            })
            ->whereHas('event', fn ($q) => $q
                ->where('is_published', true)
                ->where('starts_at', '>=', now()))
            ->get()
            ->unique('id')
            ->sortBy(fn ($p) => $p->event->starts_at)
            ->values();
    }

    public static function forUser(User $user): Collection
    {
        $categoryIds = $user->categories()->pluck('categories.id')->all();
        return self::forCategoryIds($categoryIds);
    }
}
