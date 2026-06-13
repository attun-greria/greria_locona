<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\ActivityCategory;
use App\Models\Municipality;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    private function publishedActivity(array $overrides = []): Activity
    {
        $municipality = Municipality::create([
            'name' => 'テスト町', 'slug' => 'test-town', 'prefecture' => 'テスト県',
            'is_published' => true,
        ]);
        $category = ActivityCategory::create(['name' => '農業', 'slug' => 'agri']);

        return Activity::create(array_merge([
            'municipality_id' => $municipality->id,
            'category_id' => $category->id,
            'title' => '農業体験イベント',
            'slug' => 'agri-event',
            'summary' => '田植え体験です',
            'source_url' => 'https://example.com/event',
            'status' => 'published',
            'verified_at' => now(),
            'application_deadline' => now()->addDays(10),
        ], $overrides));
    }

    public function test_home_page_renders(): void
    {
        $this->get('/')->assertOk()->assertSee('LOCONA');
    }

    public function test_published_activity_is_listed_and_viewable(): void
    {
        $activity = $this->publishedActivity();

        $this->get('/activities')->assertOk()->assertSee('農業体験イベント');
        $this->get("/activities/{$activity->slug}")->assertOk()->assertSee('田植え体験です');
    }

    public function test_draft_activity_returns_404(): void
    {
        $activity = $this->publishedActivity(['slug' => 'draft-one', 'status' => 'draft']);

        $this->get("/activities/{$activity->slug}")->assertNotFound();
    }

    public function test_keyword_search_filters_results(): void
    {
        $this->publishedActivity();

        $this->get('/activities?q=田植え')->assertOk()->assertSee('農業体験イベント');
        $this->get('/activities?q=存在しない条件')->assertOk()->assertSee('見つかりませんでした');
    }

    public function test_outbound_click_is_recorded(): void
    {
        $activity = $this->publishedActivity();

        $this->get("/go/{$activity->slug}")
            ->assertRedirect('https://example.com/event');

        $this->assertDatabaseHas('outbound_clicks', ['activity_id' => $activity->id]);
        $this->assertEquals(1, $activity->fresh()->click_count);
    }

    public function test_expired_activity_excluded_from_list(): void
    {
        $this->publishedActivity([
            'slug' => 'expired', 'title' => '終了イベント',
            'application_deadline' => now()->subDays(5),
            'end_at' => now()->subDays(5),
        ]);

        $this->get('/activities')->assertOk()->assertDontSee('終了イベント');
    }
}
