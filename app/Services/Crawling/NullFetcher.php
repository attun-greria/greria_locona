<?php

namespace App\Services\Crawling;

use App\Services\Crawling\Contracts\Fetcher;

/**
 * 既定のFetcher（安全側）。実際の外部取得は行わない。
 * 規約・robots確認とネットワーク方針が整うまでは、これが既定。
 */
class NullFetcher implements Fetcher
{
    public function fetch(string $url): FetchResult
    {
        return FetchResult::failure(null, 'fetcher disabled (LOCONA_FETCHER=null)。規約・robots確認後に http を有効化してください。');
    }
}
