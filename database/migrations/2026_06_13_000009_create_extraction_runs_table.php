<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * AI抽出履歴（CRW-008/010 / JOB-003 / ADM-008 / 10-1 extraction_runs）。
 * モデル・プロンプト版・原文・抽出JSON・信頼度・確認状態を保持する。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extraction_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crawl_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('activity_id')->nullable()->constrained()->nullOnDelete();
            $table->string('model', 128)->nullable();        // 抽出モデル
            $table->string('prompt_version', 32)->nullable();// プロンプト版
            $table->longText('source_text')->nullable();     // 抽出時点の原文（参照用）
            $table->json('extracted')->nullable();           // 抽出JSON（共通スキーマ候補）
            $table->decimal('confidence', 4, 3)->nullable(); // 信頼度（CRW-009）
            // pending / approved / edited / rejected（採用・修正・却下）
            $table->enum('review_status', ['pending', 'approved', 'edited', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index('review_status');
            $table->index('confidence');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extraction_runs');
    }
};
