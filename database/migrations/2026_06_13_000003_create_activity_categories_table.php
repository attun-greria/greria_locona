<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 活動カテゴリ（10-1 / ADM-006）。
 * 農業・食・自然・観光・文化・子育て・副業 等。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon', 32)->nullable();        // テーマ別入口の表示用
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('display_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_categories');
    }
};
