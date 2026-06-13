<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 自治体LINE設定（LIN-002 / 10-1 line_channels）。
 * シークレット等の秘密情報そのものは保持せず、安全な保管先の参照キーを持つ（SEC-006）。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('line_channels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('municipality_id')->constrained()->cascadeOnDelete();
            $table->string('channel_name');
            $table->string('channel_id')->nullable();
            // チャネルシークレット/アクセストークンはVault等の参照キーで保持（平文を入れない）
            $table->string('secret_ref')->nullable();
            $table->string('webhook_path')->nullable();    // /webhooks/line/{channel}
            $table->enum('status', ['active', 'inactive'])->default('inactive');
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('line_channels');
    }
};
