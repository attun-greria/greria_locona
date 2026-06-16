<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\CrawlRun;
use App\Models\ExtractionRun;
use App\Models\Municipality;
use App\Models\Source;
use App\Services\Extraction\ActivityDraftFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicationPolicyTest extends TestCase
{
    use RefreshDatabase;

    private function municipality(): Municipality
    {
        return Municipality::create(['name' => '町', 'slug' => 'town', 'prefecture' => '県', 'is_published' => true]);
    }

    private function activity(Municipality $m, string $visibility): Activity
    {
        return Activity::create([
            'municipality_id' => $m->id, 'title' => '内部'.$visibility, 'slug' => 'a-'.$visibility,
            'summary' => 'x', 'source_url' => 'https://example.com/'.$visibility,
            'status' => 'published', 'visibility' => $visibility, 'verified_at' => now(),
        ]);
    }

    public function test_internal_activity_is_hidden_from_public(): void
    {
        $m = $this->municipality();
        $this->activity($m, 'public');
        $internal = $this->activity($m, 'internal');

        // 一覧・詳細・API・送客で internal は出ない
        $this->get('/activities')->assertOk()->assertDontSee('内部internal');
        $this->get("/activities/{$internal->slug}")->assertNotFound();
        $this->getJson("/api/activities/{$internal->slug}")->assertNotFound();
        $this->get("/go/{$internal->slug}")->assertNotFound();

        // public スコープは1件のみ
        $this->assertSame(1, Activity::public()->count());
    }

    public function test_internal_only_source_produces_internal_activity(): void
    {
        $m = $this->municipality();
        $source = Source::create([
            'url' => 'https://example.com/sns', 'municipality_id' => $m->id,
            'page_type' => 'sns', 'license_tier' => 'restricted', 'publication_policy' => 'internal_only',
            'attribution_name' => '民間情報', 'crawl_frequency' => 'manual', 'priority' => 4,
            'is_active' => true, 'robots_checked' => true, 'terms_checked' => true,
        ]);
        $crawl = CrawlRun::create(['source_id' => $source->id, 'http_status' => 200, 'result' => 'changed', 'diff_status' => 'new', 'content_type' => 'html', 'fetched_at' => now()]);
        $extraction = ExtractionRun::create([
            'crawl_run_id' => $crawl->id, 'extracted' => ['title' => '民間イベント', 'summary' => 's'],
            'confidence' => 0.7, 'review_status' => 'pending',
        ]);

        $activity = (new ActivityDraftFactory())->fromExtraction($extraction);

        $this->assertNotNull($activity);
        $this->assertSame('internal', $activity->visibility);
        $this->assertSame('民間情報', $activity->attribution_name);
    }

    public function test_detail_shows_attribution(): void
    {
        $m = $this->municipality();
        $a = Activity::create([
            'municipality_id' => $m->id, 'title' => '出典テスト', 'slug' => 'attr', 'summary' => 'x',
            'source_url' => 'https://example.com/e', 'attribution_name' => '○○市公式サイト',
            'cited_at' => now(), 'status' => 'published', 'visibility' => 'public', 'verified_at' => now(),
            'quote_text' => '引用テキストです', 'quote_source' => '○○市サイト',
        ]);

        $this->get("/activities/{$a->slug}")->assertOk()
            ->assertSee('○○市公式サイト')
            ->assertSee('引用テキストです')
            ->assertSee('LOCONA編集部');
    }
}
