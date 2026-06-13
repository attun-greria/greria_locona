<?php

namespace App\Services\Crawling;

use App\Services\Crawling\Contracts\Fetcher;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * 実HTTP取得（CRW-002/003）。LOCONA_FETCHER=http で有効化。
 *
 * 注意：本クラスを有効化する前に、対象サイトごとに利用規約・robots.txt・
 * アクセス頻度を確認すること（CRW-004/005、法務レビュー論点）。
 * 本文・画像の転載は避け、事実情報の抽出と一次情報への送客を基本とする。
 */
class HttpFetcher implements Fetcher
{
    public function __construct(
        private readonly string $userAgent,
        private readonly int $timeout,
    ) {}

    public function fetch(string $url): FetchResult
    {
        try {
            $response = Http::withHeaders(['User-Agent' => $this->userAgent])
                ->timeout($this->timeout)
                ->retry(1, 1000, throw: false)
                ->get($url);

            $contentTypeHeader = strtolower($response->header('Content-Type'));
            $type = match (true) {
                str_contains($contentTypeHeader, 'pdf') => 'pdf',
                str_contains($contentTypeHeader, 'html'), str_contains($contentTypeHeader, 'text') => 'html',
                default => 'other',
            };

            if ($response->failed()) {
                return FetchResult::failure($response->status(), "HTTP {$response->status()}");
            }

            $charset = null;
            if (preg_match('/charset=([\w\-]+)/i', $contentTypeHeader, $m)) {
                $charset = strtolower($m[1]);
            }

            return FetchResult::success($response->status(), $response->body(), $type, $charset);
        } catch (Throwable $e) {
            return FetchResult::failure(null, $e->getMessage());
        }
    }
}
