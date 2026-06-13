<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 活動とタグの中間テーブル（10-3 多対多 / 付録B activity_tag）。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_tag', function (Blueprint $table) {
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['activity_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_tag');
    }
};
