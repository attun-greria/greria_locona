<?php

namespace App\Services\Crawling\Contracts;

use App\Services\Crawling\FetchResult;

/**
 * 取得アダプタの契約（CRW-002 / 差し替え可能）。
 */
interface Fetcher
{
    public function fetch(string $url): FetchResult;
}
