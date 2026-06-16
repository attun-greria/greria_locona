<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 活動の公開可視性と出所明示・引用（著作権対応）。
 * visibility: public=公開メディアに掲載 / internal=社内診断のみ（営業材料・非公開）
 * attribution_*: 出所明示（出典名・取得日）。quote_*: 適法引用ブロック（明瞭区別・出所明示）。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->enum('visibility', ['public', 'internal'])->default('public')->after('status');
            $table->string('attribution_name')->nullable()->after('source_url'); // 出典名（例：飯山市公式サイト）
            $table->date('cited_at')->nullable()->after('attribution_name');      // 出典の取得・確認日
            $table->text('quote_text')->nullable()->after('description');         // 引用テキスト（任意）
            $table->string('quote_source')->nullable()->after('quote_text');      // 引用の出所

            $table->index(['visibility', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropIndex(['visibility', 'status']);
            $table->dropColumn(['visibility', 'attribution_name', 'cited_at', 'quote_text', 'quote_source']);
        });
    }
};
