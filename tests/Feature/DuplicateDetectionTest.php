<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Municipality;
use App\Support\DuplicateFinder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DuplicateDetectionTest extends TestCase
{
    use RefreshDatabase;

    private function municipality(): Municipality
    {
        return Municipality::create(['name' => '町', 'slug' => 'town', 'prefecture' => '県', 'is_published' => true]);
    }

    private function activity(Municipality $m, array $o): Activity
    {
        return Activity::create(array_merge([
            'municipality_id' => $m->id, 'summary' => 'x', 'source_url' => 'https://example.com/'.uniqid(),
            'status' => 'published',
        ], $o));
    }

    public function test_detects_same_source_url(): void
    {
        $m = $this->municipality();
        $this->activity($m, ['title' => 'A', 'slug' => 'a', 'source_url' => 'https://example.com/same']);
        $this->activity($m, ['title' => 'B', 'slug' => 'b', 'source_url' => 'https://example.com/same']);

        $groups = (new DuplicateFinder())->candidates();
        $this->assertNotEmpty($groups);
        $this->assertSame('同一の一次情報URL', $groups[0]['reason']);
    }

    public function test_detects_similar_title_same_municipality(): void
    {
        $m = $this->municipality();
        $this->activity($m, ['title' => '棚田の米づくり体験', 'slug' => 'a']);
        $this->activity($m, ['title' => '棚田の米づくり体験（複製）', 'slug' => 'b']);

        $reasons = collect((new DuplicateFinder())->candidates())->pluck('reason');
        $this->assertTrue($reasons->contains('類似タイトル（同一自治体）'));
    }

    public function test_no_false_positive_for_distinct_activities(): void
    {
        $m = $this->municipality();
        $this->activity($m, ['title' => '農業体験', 'slug' => 'a', 'start_at' => now()->addDays(10)]);
        $this->activity($m, ['title' => '全く別の副業募集案件', 'slug' => 'b', 'start_at' => now()->addDays(40)]);

        $this->assertEmpty((new DuplicateFinder())->candidates());
    }
}
