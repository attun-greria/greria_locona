<?php

namespace App\Jobs;

use App\Models\CrawlRun;
use App\Models\ExtractionRun;
use App\Services\Extraction\Contracts\Extractor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

/**
 * 取得スナップショットからのAI抽出（JOB-003 / CRW-008/010）。
 * 抽出結果は review_status=pending で保存し、必ず人手確認（ADM-008）を経る。
 * 原文・モデル・プロンプト版・実行日時を保持（CRW-010）。
 */
class ExtractActivityJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public CrawlRun $crawlRun) {}

    public function handle(Extractor $extractor): void
    {
        $crawlRun = $this->crawlRun->loadMissing('source');

        if (! $crawlRun->snapshot_path) {
            return;
        }

        $disk = config('locona.crawl.snapshot_disk', 'local');
        if (! Storage::disk($disk)->exists($crawlRun->snapshot_path)) {
            return;
        }
        $content = Storage::disk($disk)->get($crawlRun->snapshot_path);
        $url = $crawlRun->source?->url ?? '';

        $result = $extractor->extract($content, $url);

        ExtractionRun::create([
            'crawl_run_id' => $crawlRun->id,
            'model' => config('locona.extraction.model'),
            'prompt_version' => config('locona.extraction.prompt_version'),
            'source_text' => $result->sourceExcerpt,
            'extracted' => $result->data,
            'confidence' => $result->confidence,
            'review_status' => 'pending',
        ]);
    }
}
