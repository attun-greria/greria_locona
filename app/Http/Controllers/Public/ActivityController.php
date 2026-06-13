<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityCategory;
use App\Models\Municipality;
use Illuminate\Http\Request;

/**
 * 活動一覧・検索・詳細（PUB-002〜005, 008, 009）。
 */
class ActivityController extends Controller
{
    /**
     * 活動一覧・検索（PUB-002/003/004/009）。
     * キーワード、都道府県、市区町村、カテゴリ、開催時期、参加条件で絞り込む。
     */
    public function index(Request $request)
    {
        $query = Activity::published()->notExpired()
            ->with(['municipality', 'category']);

        // キーワード（活動名・要約・主催者）
        if ($keyword = trim((string) $request->input('q', ''))) {
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('summary', 'like', "%{$keyword}%")
                    ->orWhere('organizer_name', 'like', "%{$keyword}%");
            });
        }

        // 地域
        if ($pref = $request->input('prefecture')) {
            $query->whereHas('municipality', fn ($q) => $q->where('prefecture', $pref));
        }
        if ($city = $request->input('city')) {
            $query->whereHas('municipality', fn ($q) => $q->where('city', $city));
        }

        // カテゴリ
        if ($category = $request->input('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $category));
        }

        // 開催時期（締切または開催開始が指定範囲内）
        if ($from = $request->date('from')) {
            $query->where(function ($q) use ($from) {
                $q->where('start_at', '>=', $from)->orWhere('application_deadline', '>=', $from);
            });
        }
        if ($to = $request->date('to')) {
            $query->where(function ($q) use ($to) {
                $q->where('start_at', '<=', $to)->orWhere('application_deadline', '<=', $to);
            });
        }

        // 参加条件（PUB-004）
        foreach (['child_friendly', 'beginner_friendly', 'online_available', 'has_reward', 'transport_support', 'lodging_support'] as $flag) {
            if ($request->boolean($flag)) {
                $query->where($flag, true);
            }
        }
        if ($request->input('recurrence') === 'recurring') {
            $query->where('is_recurring', true);
        } elseif ($request->input('recurrence') === 'single') {
            $query->where('is_recurring', false);
        }

        // 並び替え
        match ($request->input('sort')) {
            'deadline' => $query->orderByRaw('application_deadline is null, application_deadline asc'),
            'popular' => $query->orderByDesc('click_count'),
            default => $query->latest('verified_at'),
        };

        $activities = $query->paginate(12)->withQueryString();

        $categories = ActivityCategory::where('is_active', true)->orderBy('display_order')->get();
        $prefectures = Municipality::where('is_published', true)
            ->select('prefecture')->distinct()->orderBy('prefecture')->pluck('prefecture');

        return view('public.activities.index', compact('activities', 'categories', 'prefectures'));
    }

    /**
     * 活動詳細（PUB-005/008/010）。
     */
    public function show(Activity $activity)
    {
        abort_unless($activity->status === 'published', 404);

        $activity->load(['municipality', 'category', 'tags']);

        // 関連活動（PUB-008）: 同カテゴリ・同自治体を優先
        $related = Activity::published()->notExpired()
            ->where('id', '!=', $activity->id)
            ->where(function ($q) use ($activity) {
                $q->where('category_id', $activity->category_id)
                    ->orWhere('municipality_id', $activity->municipality_id);
            })
            ->with(['municipality', 'category'])
            ->take(4)
            ->get();

        return view('public.activities.show', compact('activity', 'related'));
    }
}
