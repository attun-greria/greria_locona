<?php

namespace App\Services\Extraction\Contracts;

use App\Services\Extraction\ExtractionResult;

/**
 * AI抽出アダプタの契約（CRW-008 / 差し替え可能）。
 */
interface Extractor
{
    public function extract(string $content, string $url): ExtractionResult;
}
