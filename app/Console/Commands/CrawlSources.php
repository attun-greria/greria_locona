<?php

namespace App\Console\Commands;

use App\Models\Source;
use Illuminate\Console\Command;

/**
 * 収集元URLの定期取得（JOB-001 / CRW-002〜006）。
 *
 * 注意：実際の取得は、対象サイトごとの利用規約・robots.txt・アクセス頻度の確認（CRW-004/005）と
 * 取得・差分検知・AI抽出ジョブの実装（NFR-005: Webリクエストから分離した非同期実行）を前提とする。
 * 本コマンドは収集対象の抽出と取得ジョブのディスパッチ位置を示すスケルトン。
 * 取得本体は App\Jobs\* として実装し、Queue へ投入する。
 */
class CrawlSources extends Command
{
    protected $signature = 'locona:crawl {--frequency=daily : 対象とする取得頻度（daily/weekly/monthly）}';

    protected $description = '対象URLを取得対象として抽出し、取得ジョブを投入する（スケルトン）';

    public function handle(): int
    {
        $frequency = $this->option('frequency');

        $sources = Source::where('is_active', true)
            ->where('crawl_frequency', $frequency)
            ->where('robots_checked', true)   // robots未確認は対象外（CRW-005）
            ->where('terms_checked', true)    // 規約未確認は対象外
            ->orderBy('priority')
            ->get();

        $this->info("取得対象（{$frequency}）：{$sources->count()}件");

        foreach ($sources as $source) {
            // TODO: 取得・差分検知・AI抽出を非同期ジョブとして投入する
            // dispatch(new \App\Jobs\FetchSource($source));
            $this->line("  queued: {$source->url}");
        }

        $this->comment('※ 取得本体は規約・robots確認後に App\\Jobs として実装してください。');

        return self::SUCCESS;
    }
}
