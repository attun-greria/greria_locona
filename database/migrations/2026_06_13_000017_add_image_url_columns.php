<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 任意の画像URL（権利を確認した画像のみ掲載）。
 * 未設定時はカテゴリ連動の生成カバーを表示する（無断転載を避ける基本方針）。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->string('image_url', 1024)->nullable()->after('apply_url');
        });
        Schema::table('municipalities', function (Blueprint $table) {
            $table->string('image_url', 1024)->nullable()->after('official_url');
            $table->string('catchphrase')->nullable()->after('name'); // 紹介キャッチコピー
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn('image_url');
        });
        Schema::table('municipalities', function (Blueprint $table) {
            $table->dropColumn(['image_url', 'catchphrase']);
        });
    }
};
