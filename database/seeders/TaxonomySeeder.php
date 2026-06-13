<?php

namespace Database\Seeders;

use App\Models\ActivityCategory;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * カテゴリ・タグ初期データ（6-1 検索軸 / 9-1 LINEタグ）。
 */
class TaxonomySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['農業・食', '🌾'], ['自然・アウトドア', '🏕'], ['観光・体験', '🗺'],
            ['文化・祭り', '🎏'], ['子育て・教育', '👨‍👩‍👧'], ['副業・プロボノ', '💼'],
            ['空き家・二地域居住', '🏠'], ['ボランティア', '🤝'],
        ];
        foreach ($categories as $i => [$name, $icon]) {
            ActivityCategory::updateOrCreate(
                ['slug' => Str::slug($name) ?: 'cat-'.$i],
                ['name' => $name, 'icon' => $icon, 'display_order' => $i, 'is_active' => true]
            );
        }

        $searchTags = ['週末', '初心者歓迎', '親子向け', '日帰り', '宿泊あり', 'リモート参加', '継続活動'];
        foreach ($searchTags as $name) {
            Tag::updateOrCreate(['slug' => Str::slug($name) ?: 'tag-'.md5($name)], ['name' => $name, 'type' => 'search']);
        }

        $lineTags = ['農業', '食', '祭り', '自然', '子育て', '副業', '空き家', '二地域居住', '移住相談'];
        foreach ($lineTags as $name) {
            Tag::updateOrCreate(['slug' => 'line-'.(Str::slug($name) ?: md5($name))], ['name' => $name, 'type' => 'line']);
        }
    }
}
