<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 自治体（10-1 / 付録B）。
 * 都道府県・市区町村・概要・公式URL・関連サイト・LINE導線・相談窓口を保持する。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('municipalities', function (Blueprint $table) {
            $table->id();
            $table->string('name');                       // 自治体名
            $table->string('slug')->unique();             // 公開URL用スラッグ
            $table->string('prefecture', 16);             // 都道府県
            $table->string('city', 64)->nullable();       // 市区町村
            $table->text('summary')->nullable();          // 自治体概要
            $table->string('official_url')->nullable();   // 公式サイト
            $table->json('related_urls')->nullable();     // 関連サイト（移住・観光・DMO等）
            $table->string('line_url')->nullable();       // LINE導線（LIN-001）
            $table->string('contact_name')->nullable();   // 相談窓口名
            $table->string('contact_url')->nullable();    // 相談窓口URL
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->index('prefecture');
            $table->index('is_published');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('municipalities');
    }
};
