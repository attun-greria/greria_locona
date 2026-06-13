<?php

namespace App\Providers;

use App\Services\Crawling\Contracts\Fetcher;
use App\Services\Crawling\HttpFetcher;
use App\Services\Crawling\NullFetcher;
use App\Services\Extraction\Contracts\Extractor;
use App\Services\Extraction\HeuristicExtractor;
use App\Services\Extraction\NullExtractor;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * 収集・抽出アダプタを設定に応じてバインドする（CRW-008 差し替え可能）。
     */
    public function register(): void
    {
        $this->app->bind(Fetcher::class, function () {
            return match (config('locona.crawl.fetcher')) {
                'http' => new HttpFetcher(
                    config('locona.crawl.user_agent'),
                    (int) config('locona.crawl.timeout'),
                ),
                default => new NullFetcher(),
            };
        });

        $this->app->bind(Extractor::class, function () {
            return match (config('locona.extraction.driver')) {
                'heuristic' => new HeuristicExtractor(),
                // 'llm' => new LlmExtractor(...), // 外部LLM連携を実装する差し替え枠
                default => new NullExtractor(),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
