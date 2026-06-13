<?php

namespace App\Services\Extraction;

/**
 * AI抽出結果DTO（CRW-008/009）。
 * data: 共通スキーマ候補（title, summary, application_deadline, fee 等）
 * confidence: レコード単位の信頼度（0.0〜1.0）
 */
class ExtractionResult
{
    public function __construct(
        public readonly array $data,
        public readonly float $confidence,
        public readonly string $sourceExcerpt = '',
    ) {}
}
