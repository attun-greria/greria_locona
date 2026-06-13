<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * URL取得履歴（CRW-002/003 / JOB-001 / 10-1 crawl_runs）。
 * 原文バイナリはDBへ格納せず、ストレージ上の保存先パスとメタデータを保持する（10-3）。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crawl_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->string('content_hash', 64)->nullable();
            $table->string('charset', 32)->nullable();
            $table->string('snapshot_path')->nullable();  // ストレージ上のHTML/PDF保存先
            $table->enum('content_type', ['html', 'pdf', 'other'])->default('html');
            // success / changed / unchanged / failed
            $table->enum('result', ['success', 'changed', 'unchanged', 'failed'])->default('success');
            // 差分検知結果（CRW-006）: new / updated / removed / none
            $table->enum('diff_status', ['new', 'updated', 'removed', 'none'])->default('none');
            $table->text('error_message')->nullable();
            $table->timestamp('fetched_at')->nullable();
            $table->timestamps();

            $table->index('result');
            $table->index('diff_status');
            $table->index('fetched_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crawl_runs');
    }
};
