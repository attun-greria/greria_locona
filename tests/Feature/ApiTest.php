<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Municipality;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    private function seedActivity(array $overrides = []): Activity
    {
        $m = Municipality::firstOrCreate(
            ['slug' => 'api-town'],
            ['name' => 'API町', 'prefecture' => 'テスト県', 'is_published' => true],
        );

        return Activity::create(array_merge([
            'municipality_id' => $m->id,
            'title' => 'API活動', 'slug' => 'api-activity',
            'summary' => 'API用の活動', 'source_url' => 'https://example.com/api',
            'status' => 'published', 'verified_at' => now(),
            'application_deadline' => now()->addDays(10),
            'child_friendly' => true,
        ], $overrides));
    }

    public function test_activities_index_returns_json(): void
    {
        $this->seedActivity();

        $this->getJson('/api/activities')
            ->assertOk()
            ->assertJsonStructure(['data' => [['id', 'slug', 'title', 'summary', 'source_url', 'conditions']]])
            ->assertJsonPath('data.0.title', 'API活動');
    }

    public function test_activities_index_applies_condition_filter(): void
    {
        $this->seedActivity();
        $this->seedActivity(['slug' => 'no-child', 'title' => '大人向け', 'child_friendly' => false]);

        $res = $this->getJson('/api/activities?child_friendly=1')->assertOk();
        $titles = collect($res->json('data'))->pluck('title');
        $this->assertContains('API活動', $titles);
        $this->assertNotContains('大人向け', $titles);
    }

    public function test_activity_show_returns_published_only(): void
    {
        $published = $this->seedActivity();
        $draft = $this->seedActivity(['slug' => 'draft-api', 'status' => 'draft']);

        $this->getJson("/api/activities/{$published->slug}")->assertOk()->assertJsonPath('data.slug', 'api-activity');
        $this->getJson("/api/activities/{$draft->slug}")->assertNotFound();
    }

    public function test_municipalities_index_returns_json(): void
    {
        $this->seedActivity();

        $this->getJson('/api/municipalities')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'API町')
            ->assertJsonPath('data.0.activities_count', 1);
    }
}
