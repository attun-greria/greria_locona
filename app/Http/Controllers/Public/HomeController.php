<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityCategory;
use App\Models\Municipality;

/**
 * トップページ（PUB-001 / PUB-01）。
 * ポータル構成：テーマ別入口・締切間近・注目活動・最新活動・自治体紹介・地域入口。
 */
class HomeController extends Controller
{
    public function index()
    {
        $categories = ActivityCategory::where('is_active', true)
            ->orderBy('display_order')
            ->withCount(['activities' => fn ($q) => $q->published()->notExpired()])
            ->get();

        $base = Activity::published()->notExpired()->with(['municipality', 'category']);

        $featured = (clone $base)->orderByDesc('click_count')->take(6)->get();
        $latest = (clone $base)->latest('verified_at')->take(8)->get();

        // 締切が近い活動（ポータルの時間軸セクション）
        $deadlineSoon = (clone $base)
            ->whereNotNull('application_deadline')
            ->where('application_deadline', '>=', now()->startOfDay())
            ->orderBy('application_deadline')
            ->take(4)->get();

        // 注目の自治体（紹介コンテンツ）
        $municipalities = Municipality::where('is_published', true)
            ->withCount(['activities' => fn ($q) => $q->published()->notExpired()])
            ->orderByDesc('activities_count')
            ->take(6)
            ->get();

        // 地域（都道府県）入口
        $prefectures = Municipality::where('is_published', true)
            ->select('prefecture')->distinct()->orderBy('prefecture')->pluck('prefecture');

        // ポータルの実績ストリップ
        $stats = [
            'activities' => Activity::published()->notExpired()->count(),
            'municipalities' => Municipality::where('is_published', true)->count(),
            'categories' => $categories->count(),
        ];

        return view('public.home', compact(
            'categories', 'featured', 'latest', 'deadlineSoon', 'municipalities', 'prefectures', 'stats'
        ));
    }
}
