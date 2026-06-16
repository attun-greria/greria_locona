<?php

namespace App\Console\Commands;

use App\Models\Activity;
use App\Support\Audit;
use Illuminate\Console\Command;

/**
 * 期限切れ活動の検知（JOB-004 / CRW-012 / ADM-010）。
 * 締切・開催日を過ぎた公開中の活動を検知し、--archive 指定時はアーカイブする。
 * 既定はドライラン（件数の報告のみ。運用担当が確認画面で対応）。
 */
class DetectExpiredActivities extends Command
{
    protected $signature = 'locona:detect-expired {--archive : 期限切れ活動を自動でarchiveする}';

    protected $description = '期限切れ（締切・開催日経過）の公開中活動を検知する';

    public function handle(): int
    {
        $today = now()->startOfDay();

        $expired = Activity::public()
            ->where('kind', 'event') // 制度・相談は常設のため対象外
            ->where('is_recurring', false)
            ->where(function ($q) use ($today) {
                $q->where('application_deadline', '<', $today)
                    ->orWhere('end_at', '<', $today);
            })
            ->get();

        $this->info("期限切れ候補：{$expired->count()}件");

        foreach ($expired as $activity) {
            $this->line("  - [{$activity->id}] {$activity->title}");
        }

        if ($this->option('archive')) {
            foreach ($expired as $activity) {
                $activity->update(['status' => 'archived']);
                Audit::log('auto_archived_expired', $activity);
            }
            $this->info("{$expired->count()}件をアーカイブしました。");
        }

        return self::SUCCESS;
    }
}
