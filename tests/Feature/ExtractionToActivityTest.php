<?php

namespace Tests\Feature;

use App\Models\CrawlRun;
use App\Models\ExtractionRun;
use App\Models\Municipality;
use App\Models\Source;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ExtractionToActivityTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name' => 'A', 'email' => 'a@locona.test',
            'password' => Hash::make('password'), 'role' => User::ROLE_ADMIN, 'is_active' => true,
        ]);
    }

    private function pendingExtraction(?int $municipalityId): ExtractionRun
    {
        $source = Source::create([
            'url' => 'https://example.com/e', 'municipality_id' => $municipalityId,
            'page_type' => 'official', 'crawl_frequency' => 'weekly', 'priority' => 3,
            'is_active' => true, 'robots_checked' => true, 'terms_checked' => true,
        ]);
        $crawl = CrawlRun::create([
            'source_id' => $source->id, 'http_status' => 200, 'result' => 'changed',
            'diff_status' => 'new', 'content_type' => 'html', 'fetched_at' => now(),
        ]);

        return ExtractionRun::create([
            'crawl_run_id' => $crawl->id, 'model' => 'heuristic-v1', 'prompt_version' => 'v1',
            'extracted' => [
                'title' => '田植え体験', 'summary' => '親子で田植え',
                'application_deadline' => '2026-06-20', 'fee_text' => '無料',
                'child_friendly' => true, 'source_url' => 'https://example.com/e',
            ],
            'confidence' => 0.8, 'review_status' => 'pending',
        ]);
    }

    public function test_approving_creates_draft_activity_and_links(): void
    {
        $m = Municipality::create(['name' => '町', 'slug' => 'town', 'prefecture' => '県', 'is_published' => true]);
        $extraction = $this->pendingExtraction($m->id);

        $this->actingAs($this->admin())
            ->post(route('admin.extractions.review', $extraction), ['decision' => 'approved'])
            ->assertRedirect();

        $this->assertDatabaseHas('activities', [
            'title' => '田植え体験', 'municipality_id' => $m->id, 'status' => 'draft', 'child_friendly' => true,
        ]);
        $activity = \App\Models\Activity::first();
        $this->assertEquals($activity->id, $extraction->fresh()->activity_id);
        $this->assertEquals('approved', $extraction->fresh()->review_status);
    }

    public function test_approving_without_municipality_does_not_create_activity(): void
    {
        $extraction = $this->pendingExtraction(null);

        $this->actingAs($this->admin())
            ->post(route('admin.extractions.review', $extraction), ['decision' => 'approved'])
            ->assertRedirect(route('admin.extractions.index'));

        $this->assertEquals(0, \App\Models\Activity::count());
    }

    public function test_rejecting_does_not_create_activity(): void
    {
        $m = Municipality::create(['name' => '町', 'slug' => 'town', 'prefecture' => '県', 'is_published' => true]);
        $extraction = $this->pendingExtraction($m->id);

        $this->actingAs($this->admin())
            ->post(route('admin.extractions.review', $extraction), ['decision' => 'rejected'])
            ->assertRedirect(route('admin.extractions.index'));

        $this->assertEquals(0, \App\Models\Activity::count());
    }
}
