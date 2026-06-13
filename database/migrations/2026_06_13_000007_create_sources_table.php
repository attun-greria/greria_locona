<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 収集元URL（CRW-001 / ADM-007 / 10-1 sources）。
 * URL・自治体・種別・取得頻度・規約/robots確認・最終取得を管理。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('municipality_id')->nullable()->constrained()->nullOnDelete();
            $table->string('url', 1024);
            // official / tourism / migration / pdf / sns / other
            $table->enum('page_type', ['official', 'tourism', 'migration', 'pdf', 'sns', 'other'])->default('official');
            // daily / weekly / monthly / manual
            $table->enum('crawl_frequency', ['daily', 'weekly', 'monthly', 'manual'])->default('weekly');
            $table->unsignedTinyInteger('priority')->default(3);  // 1(高)〜5(低)
            $table->boolean('is_active')->default(true);

            // 法務・規約確認（CRW-005）
            $table->boolean('robots_checked')->default(false);
            $table->boolean('terms_checked')->default(false);
            $table->text('terms_note')->nullable();

            // 取得状態
            $table->timestamp('last_crawled_at')->nullable();
            $table->string('last_content_hash', 64)->nullable();
            $table->unsignedInteger('failure_count')->default(0);

            $table->timestamps();

            $table->index('page_type');
            $table->index('crawl_frequency');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sources');
    }
};
