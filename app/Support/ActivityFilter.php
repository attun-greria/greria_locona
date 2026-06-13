<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

/**
 * 活動検索の絞り込みロジック（PUB-003/004 / 6-1 検索軸）。
 * Web画面とAPIで共通利用するため、サービス層に分離する（11-1 責務分離）。
 */
class ActivityFilter
{
    /** 参加条件のbooleanフラグ（PUB-004） */
    public const CONDITION_FLAGS = [
        'child_friendly', 'beginner_friendly', 'online_available',
        'has_reward', 'transport_support', 'lodging_support',
    ];

    /**
     * @param  array<string,mixed>  $filters
     */
    public static function apply(Builder $query, array $filters): Builder
    {
        if (! empty($filters['q'])) {
            $keyword = trim((string) $filters['q']);
            $query->where(function (Builder $q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('summary', 'like', "%{$keyword}%")
                    ->orWhere('organizer_name', 'like', "%{$keyword}%");
            });
        }

        if (! empty($filters['prefecture'])) {
            $query->whereHas('municipality', fn (Builder $q) => $q->where('prefecture', $filters['prefecture']));
        }
        if (! empty($filters['city'])) {
            $query->whereHas('municipality', fn (Builder $q) => $q->where('city', $filters['city']));
        }
        if (! empty($filters['municipality'])) {
            $query->whereHas('municipality', fn (Builder $q) => $q->where('slug', $filters['municipality']));
        }
        if (! empty($filters['category'])) {
            $query->whereHas('category', fn (Builder $q) => $q->where('slug', $filters['category']));
        }

        if (! empty($filters['from'])) {
            $query->where(function (Builder $q) use ($filters) {
                $q->where('start_at', '>=', $filters['from'])->orWhere('application_deadline', '>=', $filters['from']);
            });
        }
        if (! empty($filters['to'])) {
            $query->where(function (Builder $q) use ($filters) {
                $q->where('start_at', '<=', $filters['to'])->orWhere('application_deadline', '<=', $filters['to']);
            });
        }

        foreach (self::CONDITION_FLAGS as $flag) {
            if (! empty($filters[$flag])) {
                $query->where($flag, true);
            }
        }

        if (($filters['recurrence'] ?? null) === 'recurring') {
            $query->where('is_recurring', true);
        } elseif (($filters['recurrence'] ?? null) === 'single') {
            $query->where('is_recurring', false);
        }

        return match ($filters['sort'] ?? null) {
            'deadline' => $query->orderByRaw('application_deadline is null, application_deadline asc'),
            'popular' => $query->orderByDesc('click_count'),
            default => $query->latest('verified_at'),
        };
    }
}
