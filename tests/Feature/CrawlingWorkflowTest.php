<?php

namespace Tests\Feature;

use App\Jobs\FetchSourceJob;
use App\Models\Source;
use App\Services\Crawling\Contracts\Fetcher;
use App\Services\Crawling\FetchResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CrawlingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function bindFetcher(FetchResult $result): void
    {
        $this->app->instance(Fetcher::class, new class($result) implements Fetcher
        {
            public function __construct(private FetchResult $result) {}

            public function fetch(string $url): FetchResult
            {
                return $this->result;
            }
        });
    }

    private function source(array $overrides = []): Source
    {
        return Source::create(array_merge([
            'url' => 'https://example.com/events',
            'page_type' => 'official', 'crawl_frequency' => 'weekly', 'priority' => 2,
            'is_active' => true, 'robots_checked' => true, 'terms_checked' => true,
        ], $overrides));
    }

    private function html(): string
    {
        return <<<'HTML'
        <html><head><title>農業体験イベント</title>
        <meta name="description" content="親子で楽しむ田植え体験">
        <script type="application/ld+json">{"@type":"Event","name":"田植え体験","startDate":"2026-07-01"}</script>
        </head><body>参加費 無料 申込締切 2026年6月20日 オンライン可</body></html>
        HTML;
    }

    public function test_fetch_creates_crawl_run_and_extraction(): void
    {
        Storage::fake('local');
        $this->bindFetcher(FetchResult::success(200, $this->html()));
        $source = $this->source();

        FetchSourceJob::dispatchSync($source);

        $this->assertDatabaseHas('crawl_runs', ['source_id' => $source->id, 'diff_status' => 'new', 'result' => 'changed']);
        // QUEUE=sync なので抽出ジョブも同期実行され、pendingの抽出が生成される
        $this->assertDatabaseHas('extraction_runs', ['review_status' => 'pending']);

        $source->refresh();
        $this->assertNotNull($source->last_content_hash);
        $this->assertEquals(0, $source->failure_count);

        $run = $source->crawlRuns()->first();
        Storage::disk('local')->assertExists($run->snapshot_path);
    }

    public function test_unchanged_content_skips_extraction(): void
    {
        Storage::fake('local');
        $this->bindFetcher(FetchResult::success(200, $this->html()));
        $source = $this->source();

        FetchSourceJob::dispatchSync($source);   // new
        FetchSourceJob::dispatchSync($source->refresh()); // unchanged

        $this->assertEquals(2, $source->crawlRuns()->count());
        $this->assertDatabaseHas('crawl_runs', ['diff_status' => 'none', 'result' => 'unchanged']);
        // 抽出は初回の1件のみ（再抽出されない）
        $this->assertEquals(1, \App\Models\ExtractionRun::count());
    }

    public function test_unverified_source_is_not_fetched(): void
    {
        $this->bindFetcher(FetchResult::success(200, $this->html()));
        $source = $this->source(['robots_checked' => false]);

        FetchSourceJob::dispatchSync($source);

        $this->assertEquals(0, $source->crawlRuns()->count());
    }

    public function test_failed_fetch_records_failure(): void
    {
        $this->bindFetcher(FetchResult::failure(404, 'Not Found'));
        $source = $this->source();

        FetchSourceJob::dispatchSync($source);

        $this->assertDatabaseHas('crawl_runs', ['source_id' => $source->id, 'result' => 'failed', 'http_status' => 404]);
        $this->assertEquals(1, $source->refresh()->failure_count);
    }
}
