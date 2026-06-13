<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * タグ（10-3 / 付録B）。
 * 検索用タグとLINE運用タグの用途を type で区別する。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            // search: 利用者向け検索軸 / line: LINE運用タグ / ops: 運用分類
            $table->enum('type', ['search', 'line', 'ops'])->default('search');
            $table->timestamps();

            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};
