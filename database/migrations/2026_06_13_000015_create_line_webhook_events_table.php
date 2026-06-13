<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * LINE Webhook受信イベント記録（LIN-003）。
 * 友だち追加・ブロック・メッセージ・ポストバック等を受信・記録する。
 * 個人情報は最小化し、LINE userId は自治体（チャネル）単位で扱う（SEC-009）。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('line_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('line_channel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('line_user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_type', 32);   // follow / unfollow / message / postback / other
            $table->string('line_user_ref')->nullable(); // 受信時のLINE userId（参照）
            $table->json('payload')->nullable(); // 最小限のイベント内容
            $table->timestamp('received_at')->nullable();

            $table->index('event_type');
            $table->index('received_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('line_webhook_events');
    }
};
