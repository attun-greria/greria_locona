<?php

namespace App\Services\Extraction;

use App\Services\Extraction\Contracts\Extractor;

/**
 * 抽出無効時のフォールバック（LOCONA_EXTRACTOR=null）。
 * LLM連携を実装するまでの差し替え枠でもある（CRW-008）。
 */
class NullExtractor implements Extractor
{
    public function extract(string $content, string $url): ExtractionResult
    {
        return new ExtractionResult(['source_url' => $url], 0.0, '');
    }
}
