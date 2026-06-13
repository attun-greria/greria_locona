<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * LINEユーザー（LIN-004 / 10-1 line_users / SEC-009）。
 * LINE userId を自治体単位で管理し、必要最小限の属性タグと紐づける。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('line_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('line_channel_id')->constrained()->cascadeOnDelete();
            $table->string('line_user_id');                // LINE userId（自治体単位）
            $table->boolean('consent')->default(false);    // 同意状態
            $table->json('tags')->nullable();              // 興味/参加条件/行動段階タグ
            $table->enum('follow_status', ['followed', 'blocked'])->default('followed');
            $table->timestamp('last_contact_at')->nullable();
            $table->timestamps();

            $table->unique(['line_channel_id', 'line_user_id']);
            $table->index('follow_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('line_users');
    }
};
