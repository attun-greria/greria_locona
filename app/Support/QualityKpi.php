<?php

namespace App\Support;

use App\Models\Activity;

/**
 * 情報品質KPI（14-1）。
 * 公開中の有効活動数・30日以内確認率・期限切れ残存率などを集計する（JOB-005 集計）。
 */
class QualityKpi
{
    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $today = now()->startOfDay();
        $publishedCount = Activity::published()->count();

        $verified30 = Activity::published()
            ->where('verified_at', '>=', now()->subDays(30))
            ->count();

        $expiredPublished = Activity::published()
            ->where('kind', 'event')
            ->where('is_recurring', false)
            ->where(function ($q) use ($today) {
                $q->where('application_deadline', '<', $today)->orWhere('end_at', '<', $today);
            })->count();

        $activeCount = Activity::published()->notExpired()->count();

        return [
            'published' => $publishedCount,
            'active' => $activeCount,
            // 30日以内確認率（目標80%以上）
            'verified_rate' => $this->rate($verified30, $publishedCount),
            // 期限切れ残存率（目標5%未満）
            'expired_rate' => $this->rate($expiredPublished, $publishedCount),
        ];
    }

    private function rate(int $numerator, int $denominator): ?float
    {
        if ($denominator === 0) {
            return null;
        }

        return round($numerator / $denominator * 100, 1);
    }
}
