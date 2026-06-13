<?php

namespace Tests\Feature;

use App\Models\LineChannel;
use App\Models\Municipality;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LineWebhookTest extends TestCase
{
    use RefreshDatabase;

    private string $secret = 'test-channel-secret';

    private function channel(): LineChannel
    {
        $m = Municipality::create(['name' => '町', 'slug' => 'town', 'prefecture' => '県', 'is_published' => true]);
        $channel = LineChannel::create([
            'municipality_id' => $m->id, 'channel_name' => 'テスト', 'status' => 'active',
        ]);
        config(["line.channels.{$channel->id}.secret" => $this->secret]);

        return $channel;
    }

    private function sign(string $body): string
    {
        return base64_encode(hash_hmac('sha256', $body, $this->secret, true));
    }

    private function postWebhook(LineChannel $channel, array $payload, ?string $signature = null)
    {
        $body = json_encode($payload);
        $signature ??= $this->sign($body);

        return $this->call(
            'POST',
            "/webhooks/line/{$channel->id}",
            [], [], [],
            ['HTTP_X-Line-Signature' => $signature, 'CONTENT_TYPE' => 'application/json'],
            $body,
        );
    }

    public function test_valid_follow_event_is_recorded(): void
    {
        $channel = $this->channel();

        $this->postWebhook($channel, ['events' => [
            ['type' => 'follow', 'source' => ['userId' => 'U123']],
        ]])->assertOk();

        $this->assertDatabaseHas('line_webhook_events', ['event_type' => 'follow', 'line_user_ref' => 'U123']);
        $this->assertDatabaseHas('line_users', [
            'line_channel_id' => $channel->id, 'line_user_id' => 'U123', 'follow_status' => 'followed',
        ]);
    }

    public function test_unfollow_marks_user_blocked(): void
    {
        $channel = $this->channel();
        $this->postWebhook($channel, ['events' => [['type' => 'follow', 'source' => ['userId' => 'U9']]]])->assertOk();
        $this->postWebhook($channel, ['events' => [['type' => 'unfollow', 'source' => ['userId' => 'U9']]]])->assertOk();

        $this->assertDatabaseHas('line_users', ['line_user_id' => 'U9', 'follow_status' => 'blocked']);
    }

    public function test_invalid_signature_is_rejected(): void
    {
        $channel = $this->channel();

        $this->postWebhook($channel, ['events' => [['type' => 'follow', 'source' => ['userId' => 'U1']]]], 'wrong-sig')
            ->assertStatus(401);

        $this->assertDatabaseCount('line_webhook_events', 0);
    }

    public function test_missing_secret_returns_503(): void
    {
        $m = Municipality::create(['name' => '町2', 'slug' => 'town2', 'prefecture' => '県', 'is_published' => true]);
        $channel = LineChannel::create(['municipality_id' => $m->id, 'channel_name' => 'x', 'status' => 'active']);
        config(['line.default_secret' => null]);

        $this->postWebhook($channel, ['events' => []])->assertStatus(503);
    }
}
