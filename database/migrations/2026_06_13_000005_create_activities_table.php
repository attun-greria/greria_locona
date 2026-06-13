<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 地域活動（10-1 / 10-2 活動データの必須項目）。
 * 検索性能のため自治体・カテゴリ・公開状態・締切・開催日・更新日にインデックスを設定（10-3）。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('municipality_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('activity_categories')->nullOnDelete();

            // 基本
            $table->string('title');                       // 活動名（必須）
            $table->string('slug')->unique();
            $table->text('summary');                       // 掲載用の短い要約（必須）
            $table->longText('description')->nullable();   // 補足説明
            $table->string('source_url');                  // 一次情報URL（必須）
            $table->string('apply_url')->nullable();       // 申込URL
            $table->string('organizer_name')->nullable();  // 主催者

            // 日程
            $table->date('application_deadline')->nullable(); // 申込締切
            $table->dateTime('start_at')->nullable();         // 開催開始
            $table->dateTime('end_at')->nullable();           // 開催終了
            $table->boolean('is_recurring')->default(false);  // 定期開催

            // 参加条件・支援
            $table->string('fee_text')->nullable();        // 参加費・報酬等
            $table->boolean('child_friendly')->default(false);
            $table->boolean('beginner_friendly')->default(false);
            $table->boolean('online_available')->default(false);
            $table->boolean('has_reward')->default(false);      // 報酬あり
            $table->boolean('transport_support')->default(false);
            $table->boolean('lodging_support')->default(false);
            $table->string('target_audience')->nullable();      // 対象者
            $table->unsignedInteger('capacity')->nullable();    // 定員

            // 品質・公開
            // draft / review / published / archived / rejected（ADM-005）
            $table->enum('status', ['draft', 'review', 'published', 'archived', 'rejected'])->default('draft');
            $table->timestamp('verified_at')->nullable();       // 最終確認日（必須）
            $table->decimal('extraction_confidence', 4, 3)->nullable(); // AI抽出信頼度
            $table->unsignedBigInteger('click_count')->default(0);      // 送客クリック累計

            // SEO（PUB-010）
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('application_deadline');
            $table->index('start_at');
            $table->index('verified_at');
            $table->index(['municipality_id', 'status']);
            $table->index(['category_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
