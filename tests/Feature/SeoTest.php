<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Municipality;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_lists_published_activity(): void
    {
        $m = Municipality::create(['name' => '町', 'slug' => 'town', 'prefecture' => '県', 'is_published' => true]);
        Activity::create([
            'municipality_id' => $m->id, 'title' => '活動', 'slug' => 'act',
            'summary' => 'x', 'source_url' => 'https://example.com', 'status' => 'published',
            'verified_at' => now(), 'application_deadline' => now()->addDays(5),
        ]);

        $res = $this->get('/sitemap.xml')->assertOk();
        $this->assertStringContainsString('application/xml', $res->headers->get('Content-Type'));
        $res->assertSee('/activities/act', false);
        $res->assertSee('/municipalities/town', false);
    }

    public function test_robots_disallows_admin_and_points_to_sitemap(): void
    {
        $this->get('/robots.txt')->assertOk()
            ->assertSee('Disallow: /admin')
            ->assertSee('sitemap.xml');
    }
}
