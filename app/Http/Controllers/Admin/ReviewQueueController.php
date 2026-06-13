<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\CrawlRun;

/**
 * 期限切れ・リンク切れ・取得失敗の確認（ADM-010 / CRW-012/013 / ADM-07）。
 */
class ReviewQueueController extends Controller
{
    public function index()
    {
        $today = now()->startOfDay();

        // 期限切れだが公開中の活動（情報品質KPI: 期限切れ残存率）
        $expired = Activity::published()
            ->where('kind', 'event')
            ->where('is_recurring', false)
            ->where(function ($q) use ($today) {
                $q->where('application_deadline', '<', $today)
                    ->orWhere('end_at', '<', $today);
            })
            ->with('municipality')
            ->latest('updated_at')
            ->paginate(20, ['*'], 'expired');

        // 取得失敗（CRW-013）
        $failedCrawls = CrawlRun::where('result', 'failed')
            ->with('source.municipality')
            ->latest('fetched_at')
            ->take(20)->get();

        return view('admin.review.index', compact('expired', 'failedCrawls'));
    }
}
