<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 収集元のライセンス層と公開ポリシー（著作権・事業設計の安全装置）。
 * license_tier: open=公式/オープンデータ(政府標準・CC BY等) / semi_public=観光協会・DMO・移住ポータル / restricted=民間・SNS
 * publication_policy: publishable=事実＋自社編集＋出典で公開可 / link_only=事実＋リンク送客のみ / internal_only=社内診断のみ(非公開)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sources', function (Blueprint $table) {
            $table->enum('license_tier', ['open', 'semi_public', 'restricted'])->default('semi_public')->after('page_type');
            $table->enum('publication_policy', ['publishable', 'link_only', 'internal_only'])->default('link_only')->after('license_tier');
            $table->string('attribution_name')->nullable()->after('publication_policy'); // 出典の表示名
            $table->string('license_url', 1024)->nullable()->after('attribution_name');   // 規約・ライセンスのURL

            $table->index('license_tier');
            $table->index('publication_policy');
        });
    }

    public function down(): void
    {
        Schema::table('sources', function (Blueprint $table) {
            $table->dropIndex(['license_tier']);
            $table->dropIndex(['publication_policy']);
            $table->dropColumn(['license_tier', 'publication_policy', 'attribution_name', 'license_url']);
        });
    }
};
