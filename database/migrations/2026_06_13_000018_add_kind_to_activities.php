<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 掲載種別（kind）。関係人口の入口は「活動・イベント」だけでなく
 * 「制度・支援」「相談・紹介」も含む。制度・紹介は日程を持たず常時有効。
 * event   : 活動・イベント（日程・締切あり、期限切れ対象）
 * program : 制度・支援（ふるさと納税・移住支援金・協力隊・補助金等／常設）
 * intro   : 相談・紹介（移住相談・コミュニティ・窓口／常設）
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->enum('kind', ['event', 'program', 'intro'])->default('event')->after('category_id');
            $table->index(['kind', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropIndex(['kind', 'status']);
            $table->dropColumn('kind');
        });
    }
};
