<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\CorrectionRequest;
use App\Models\Municipality;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CorrectionRequestTest extends TestCase
{
    use RefreshDatabase;

    private function activity(): Activity
    {
        $m = Municipality::create(['name' => '町', 'slug' => 'town', 'prefecture' => '県', 'is_published' => true]);

        return Activity::create([
            'municipality_id' => $m->id, 'title' => '活動', 'slug' => 'act', 'summary' => 'x',
            'source_url' => 'https://example.com', 'status' => 'published', 'verified_at' => now(),
        ]);
    }

    public function test_public_can_submit_correction_request(): void
    {
        $activity = $this->activity();

        $this->post(route('activities.correction', $activity), [
            'type' => 'deletion',
            'message' => '主催者です。掲載を取り下げてください。',
            'requester_name' => '地域団体A',
        ])->assertRedirect();

        $this->assertDatabaseHas('correction_requests', [
            'activity_id' => $activity->id, 'type' => 'deletion', 'status' => 'open',
            'municipality_id' => $activity->municipality_id,
        ]);
    }

    public function test_correction_request_requires_message(): void
    {
        $activity = $this->activity();

        $this->post(route('activities.correction', $activity), ['type' => 'correction'])
            ->assertSessionHasErrors('message');
    }

    public function test_admin_can_update_status(): void
    {
        $activity = $this->activity();
        $cr = CorrectionRequest::create([
            'activity_id' => $activity->id, 'type' => 'correction', 'message' => 'x', 'status' => 'open',
        ]);
        $admin = User::create([
            'name' => 'A', 'email' => 'a@locona.test', 'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN, 'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.corrections.update', $cr), ['status' => 'resolved', 'admin_note' => '修正済み'])
            ->assertRedirect();

        $this->assertDatabaseHas('correction_requests', ['id' => $cr->id, 'status' => 'resolved', 'admin_note' => '修正済み']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'correction_updated']);
    }
}
