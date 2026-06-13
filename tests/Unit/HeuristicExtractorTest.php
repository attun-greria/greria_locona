<?php

namespace Tests\Unit;

use App\Services\Extraction\HeuristicExtractor;
use PHPUnit\Framework\TestCase;

class HeuristicExtractorTest extends TestCase
{
    public function test_extracts_jsonld_event_with_high_confidence(): void
    {
        $html = '<html><head><title>イベント</title>'
            .'<script type="application/ld+json">{"@type":"Event","name":"収穫祭","startDate":"2026-09-10","description":"地域の収穫祭"}</script>'
            .'</head><body>参加費 無料 子連れ可</body></html>';

        $result = (new HeuristicExtractor())->extract($html, 'https://example.com/e');

        $this->assertSame('収穫祭', $result->data['title']);
        $this->assertSame('2026-09-10', $result->data['start_at']);
        $this->assertSame('無料', $result->data['fee_text']);
        $this->assertTrue($result->data['child_friendly']);
        $this->assertSame('https://example.com/e', $result->data['source_url']);
        $this->assertGreaterThanOrEqual(0.6, $result->confidence);
    }

    public function test_extracts_deadline_and_flags_from_plain_text(): void
    {
        $html = '<html><head><title>農業ボランティア募集</title></head>'
            .'<body>申込締切 2026年6月20日。オンライン可。交通費支援あり。</body></html>';

        $result = (new HeuristicExtractor())->extract($html, 'https://example.com/v');

        $this->assertSame('農業ボランティア募集', $result->data['title']);
        $this->assertSame('2026-06-20', $result->data['application_deadline']);
        $this->assertTrue($result->data['online_available']);
        $this->assertTrue($result->data['transport_support']);
    }
}
