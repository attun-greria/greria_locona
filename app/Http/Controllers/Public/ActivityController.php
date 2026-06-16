<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityCategory;
use App\Models\Municipality;
use App\Support\ActivityFilter;
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
        $query = Activity::public()->notExpired()
            ->with(['municipality', 'category']);

        // 検索の絞り込みは共通サービスへ委譲（PUB-003/004）
        ActivityFilter::apply($query, $request->all());

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
        // 社内のみ(internal)は公開ページに出さない
        abort_unless($activity->status === 'published' && $activity->visibility === 'public', 404);

        $activity->load(['municipality', 'category', 'tags']);

        // 関連活動（PUB-008）: 同カテゴリ・同自治体を優先
        $related = Activity::public()->notExpired()
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
