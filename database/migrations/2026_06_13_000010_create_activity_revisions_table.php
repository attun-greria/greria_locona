<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 活動改訂履歴（10-1 activity_revisions）。
 * 変更前後・変更理由・担当者・日時を保持する。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->index('activity_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_revisions');
    }
};
