<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Municipality;
use App\Support\QualityKpi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QualityKpiTest extends TestCase
{
    use RefreshDatabase;

    private function make(array $o): Activity
    {
        static $m;
        $m ??= Municipality::create(['name' => '町', 'slug' => 'town', 'prefecture' => '県', 'is_published' => true]);

        return Activity::create(array_merge([
            'municipality_id' => $m->id, 'summary' => 'x', 'source_url' => 'https://e.com/'.uniqid(),
            'status' => 'published',
        ], $o));
    }

    public function test_kpi_rates(): void
    {
        // 有効・30日以内確認
        $this->make(['title' => 'A', 'slug' => 'a', 'verified_at' => now()->subDays(5), 'application_deadline' => now()->addDays(10)]);
        // 期限切れだが公開中（確認は古い）
        $this->make(['title' => 'B', 'slug' => 'b', 'verified_at' => now()->subDays(60), 'application_deadline' => now()->subDays(5)]);

        $kpi = (new QualityKpi())->summary();

        $this->assertSame(2, $kpi['published']);
        $this->assertSame(1, $kpi['active']);          // Aのみ有効
        $this->assertSame(50.0, $kpi['verified_rate']); // 2件中1件が30日以内
        $this->assertSame(50.0, $kpi['expired_rate']);  // 2件中1件が期限切れ残存
    }

    public function test_kpi_handles_empty(): void
    {
        $kpi = (new QualityKpi())->summary();
        $this->assertSame(0, $kpi['published']);
        $this->assertNull($kpi['verified_rate']);
    }
}
