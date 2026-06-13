<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityCategory;
use App\Models\Municipality;

/**
 * トップページ（PUB-001 / PUB-01）。
 * テーマ別入口・注目活動・最新活動・自治体への入口を表示する。
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

        $municipalities = Municipality::where('is_published', true)
            ->withCount(['activities' => fn ($q) => $q->published()->notExpired()])
            ->orderByDesc('activities_count')
            ->take(12)
            ->get();

        return view('public.home', compact('categories', 'featured', 'latest', 'municipalities'));
    }
}
