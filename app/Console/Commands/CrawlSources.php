<?php

namespace App\Console\Commands;

use App\Jobs\FetchSourceJob;
use App\Models\Source;
use Illuminate\Console\Command;

/**
 * 収集元URLの定期取得オーケストレータ（JOB-001 / CRW-002〜007）。
 *
 * 対象サイトごとの利用規約・robots.txt確認（CRW-005）を取得の前提条件とし、
 * 確認済み・有効・対象頻度のソースだけを取得ジョブ（FetchSourceJob）へ投入する。
 * 取得本体は非同期実行（NFR-005）。--sync で同期実行（手動・テスト用）。
 */
class CrawlSources extends Command
{
    protected $signature = 'locona:crawl
        {--frequency=daily : 対象とする取得頻度（daily/weekly/monthly）}
        {--sync : キューに積まず同期実行する}
        {--source= : 特定の収集元ID（手動再取得 CRW-014）}';

    protected $description = '確認済みの収集元URLを取得ジョブへ投入する';

    public function handle(): int
    {
        $query = Source::query()
            ->where('is_active', true)
            ->where('robots_checked', true)   // robots未確認は対象外（CRW-005）
            ->where('terms_checked', true)    // 規約未確認は対象外
            ->orderBy('priority');

        if ($id = $this->option('source')) {
            $query->where('id', $id);
        } else {
            $query->where('crawl_frequency', $this->option('frequency'));
        }

        $sources = $query->get();

        if ($sources->isEmpty()) {
            $this->warn('対象の収集元がありません（規約・robots確認済みかつ有効なものが対象）。');

            return self::SUCCESS;
        }

        $sync = $this->option('sync');
        $this->info(($sync ? '同期実行' : 'キュー投入').'：'.$sources->count().'件');

        foreach ($sources as $source) {
            $sync ? FetchSourceJob::dispatchSync($source) : FetchSourceJob::dispatch($source);
            $this->line('  '.($sync ? 'fetched' : 'queued').': '.$source->url);
        }

        return self::SUCCESS;
    }
}
