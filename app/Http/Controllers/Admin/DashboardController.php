<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\CorrectionRequest;
use App\Models\CrawlRun;
use App\Models\ExtractionRun;
use App\Models\OutboundClick;
use App\Support\QualityKpi;

/**
 * ダッシュボード（ADM-013 / ADM-02）。
 * 公開活動数・確認待ち・期限切れ候補・取得失敗・クリック数を表示する。
 */
class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->startOfDay();

        $stats = [
            'published' => Activity::public()->count(),
            'review' => Activity::where('status', 'review')->count()
                + ExtractionRun::where('review_status', 'pending')->count(),
            'expired' => Activity::public()
                ->where('kind', 'event')
                ->where('is_recurring', false)
                ->where(function ($q) use ($today) {
                    $q->where('application_deadline', '<', $today)
                        ->orWhere('end_at', '<', $today);
                })->count(),
            'crawl_failed' => CrawlRun::where('result', 'failed')->count(),
            'clicks' => OutboundClick::count(),
            'corrections' => CorrectionRequest::whereIn('status', ['open', 'in_progress'])->count(),
        ];

        $recentReview = Activity::where('status', 'review')
            ->with('municipality')->latest('updated_at')->take(8)->get();

        $clicks30d = OutboundClick::where('clicked_at', '>=', now()->subDays(30))->count();
        $kpi = (new QualityKpi())->summary();

        return view('admin.dashboard', compact('stats', 'recentReview', 'clicks30d', 'kpi'));
    }
}
