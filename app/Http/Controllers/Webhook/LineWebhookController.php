<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\LineChannel;
use App\Models\LineUser;
use App\Models\LineWebhookEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * LINE Webhook受信（LIN-003 / 11-1 POST /webhooks/line/{channel}）。
 * 署名検証（SEC）後、友だち追加・ブロック・メッセージ・ポストバック等を記録する。
 * LINE userId は自治体（チャネル）単位で最小限に保持する（LIN-004 / SEC-009）。
 */
class LineWebhookController extends Controller
{
    public function handle(Request $request, LineChannel $channel): Response
    {
        $secret = config("line.channels.{$channel->id}.secret") ?? config('line.default_secret');
        abort_if(blank($secret), 503, 'LINE channel secret is not configured.');

        // 署名検証（X-Line-Signature = base64(HMAC-SHA256(body, secret)))
        $body = $request->getContent();
        $expected = base64_encode(hash_hmac('sha256', $body, $secret, true));
        $signature = (string) $request->header('X-Line-Signature', '');
        abort_unless(hash_equals($expected, $signature), 401, 'Invalid signature.');

        $events = $request->input('events', []);
        foreach ($events as $event) {
            $this->recordEvent($channel, $event);
        }

        return response('OK', 200);
    }

    private function recordEvent(LineChannel $channel, array $event): void
    {
        $type = $event['type'] ?? 'other';
        $userRef = $event['source']['userId'] ?? null;

        $lineUser = null;
        if ($userRef) {
            $lineUser = LineUser::firstOrNew([
                'line_channel_id' => $channel->id,
                'line_user_id' => $userRef,
            ]);

            match ($type) {
                'follow' => $lineUser->follow_status = 'followed',
                'unfollow' => $lineUser->follow_status = 'blocked',
                default => null,
            };
            $lineUser->last_contact_at = now();
            $lineUser->save();
        }

        LineWebhookEvent::create([
            'line_channel_id' => $channel->id,
            'line_user_id' => $lineUser?->id,
            'event_type' => in_array($type, ['follow', 'unfollow', 'message', 'postback'], true) ? $type : 'other',
            'line_user_ref' => $userRef,
            // 個人情報最小化：メッセージ本文は保持せず、種別等の最小情報のみ（SEC-008）
            'payload' => ['type' => $type, 'message_type' => $event['message']['type'] ?? null],
            'received_at' => now(),
        ]);
    }
}
