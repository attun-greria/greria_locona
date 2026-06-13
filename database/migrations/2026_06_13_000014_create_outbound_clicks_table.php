<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 一次情報への送客計測（PUB-006 / ACC-003 / 10-1 outbound_clicks）。
 * 個人識別は最小化し、活動・自治体・遷移先・日時・流入元のみを記録する（SEC-008）。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outbound_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('municipality_id')->nullable()->constrained()->nullOnDelete();
            $table->string('target_url', 1024);
            $table->enum('link_type', ['source', 'apply'])->default('source'); // 一次情報 / 申込
            $table->string('referrer')->nullable();        // 流入元（最小限）
            $table->timestamp('clicked_at')->nullable();

            $table->index('activity_id');
            $table->index('clicked_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outbound_clicks');
    }
};
