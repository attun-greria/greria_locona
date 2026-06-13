<?php

namespace App\Jobs;

use App\Models\CrawlRun;
use App\Models\Source;
use App\Services\Crawling\Contracts\Fetcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

/**
 * 収集元URLの取得＋差分検知（JOB-001/002 / CRW-002/006/013）。
 * 非同期実行（NFR-005）。取得本文はストレージへ、メタはcrawl_runsへ（10-3）。
 * 変更が検知されたら抽出ジョブ（CRW-007）を投入する。
 */
class FetchSourceJob implements ShouldQueue
{
    use Queueable;

    public int $tries;

    public function __construct(public Source $source)
    {
        $this->tries = max(1, (int) config('locona.crawl.max_retries', 3));
    }

    public function handle(Fetcher $fetcher): void
    {
        $source = $this->source;

        // ゲート：無効・規約/robots未確認は取得しない（CRW-004/005 / 法務）
        if (! $source->is_active || ! $source->robots_checked || ! $source->terms_checked) {
            return;
        }

        $result = $fetcher->fetch($source->url);

        if (! $result->ok) {
            CrawlRun::create([
                'source_id' => $source->id,
                'http_status' => $result->status,
                'content_type' => 'html',
                'result' => 'failed',
                'diff_status' => 'none',
                'error_message' => $result->error,
                'fetched_at' => now(),
            ]);
            $source->increment('failure_count');
            $source->forceFill(['last_crawled_at' => now()])->save();

            return;
        }

        $hash = $result->hash();
        $diffStatus = match (true) {
            $source->last_content_hash === null => 'new',
            $source->last_content_hash === $hash => 'unchanged',
            default => 'updated',
        };
        $changed = $diffStatus !== 'unchanged';

        // 変更時のみスナップショット保存（10-3）
        $snapshotPath = null;
        if ($changed) {
            $disk = config('locona.crawl.snapshot_disk', 'local');
            $ext = $result->contentType === 'pdf' ? 'pdf' : 'html';
            $snapshotPath = "crawl-snapshots/{$source->id}/".now()->format('Ymd_His').".{$ext}";
            Storage::disk($disk)->put($snapshotPath, $result->body);
        }

        $crawlRun = CrawlRun::create([
            'source_id' => $source->id,
            'http_status' => $result->status,
            'content_hash' => $hash,
            'charset' => $result->charset,
            'snapshot_path' => $snapshotPath,
            'content_type' => $result->contentType,
            'result' => $changed ? 'changed' : 'unchanged',
            // diff_status カラムは new/updated/removed/none。変更なしは none で記録。
            'diff_status' => $changed ? $diffStatus : 'none',
            'fetched_at' => now(),
        ]);

        $source->forceFill([
            'last_crawled_at' => now(),
            'last_content_hash' => $hash,
            'failure_count' => 0,
        ])->save();

        // 新規・更新は抽出キューへ（CRW-007）
        if (in_array($diffStatus, ['new', 'updated'], true)) {
            ExtractActivityJob::dispatch($crawlRun);
        }
    }
}
