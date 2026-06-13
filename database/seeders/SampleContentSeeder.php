<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\ActivityCategory;
use App\Models\CrawlRun;
use App\Models\ExtractionRun;
use App\Models\Municipality;
use App\Models\Source;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * 実証用サンプルデータ（MVP受入: 自治体・活動 / ACC-001）。
 * 実データではなくデモ用の構造化サンプル。
 */
class SampleContentSeeder extends Seeder
{
    public function run(): void
    {
        $categories = ActivityCategory::pluck('id', 'name');
        $searchTags = Tag::where('type', 'search')->pluck('id')->all();

        $samples = [
            ['長野県飯山市', '長野県', '飯山市', '雪と里山に恵まれた北信州のまち。農山村体験や二地域居住の受け入れに力を入れています。'],
            ['島根県海士町', '島根県', '海士町', '隠岐諸島の離島。半農半X、教育魅力化、Iターン受け入れで知られる地域です。'],
            ['徳島県神山町', '徳島県', '神山町', 'サテライトオフィスやアートで関係人口づくりを進める山あいのまち。'],
            ['北海道下川町', '北海道', '下川町', '森林資源を活かしたSDGsのまちづくり。林業・移住体験を実施。'],
        ];

        $activityTemplates = [
            ['週末ではじめる棚田の米づくり体験', '農業・食', '地元農家と一緒に田植え・稲刈りを行う日帰り体験。初心者・親子歓迎。', true, true, false],
            ['空き家を活かす二地域居住お試し滞在', '空き家・二地域居住', '町内の改修済み空き家に滞在し、暮らしと仕事を体験するモニタープログラム。', false, false, true],
            ['里山の森林整備ボランティア', 'ボランティア', '間伐や歩道整備を行う継続型の森づくり活動。道具・指導あり。', false, true, false],
            ['地域課題に関わる副業・プロボノ募集', '副業・プロボノ', '自治体のデジタル発信や商品開発をリモート中心で支援する人材を募集。', false, true, false],
            ['親子で楽しむ秋の収穫祭', '文化・祭り', '地域の収穫祭。農産物の直売や郷土食づくり体験を実施。', true, true, false],
        ];

        foreach ($samples as $mi => [$name, $pref, $city, $summary]) {
            $municipality = Municipality::updateOrCreate(
                ['slug' => Str::slug($name) ?: 'm-'.$mi],
                [
                    'name' => $name, 'prefecture' => $pref, 'city' => $city, 'summary' => $summary,
                    'official_url' => 'https://example.com/'.$city,
                    'related_urls' => ['https://example.com/'.$city.'/kanko', 'https://example.com/'.$city.'/iju'],
                    'line_url' => 'https://line.me/R/ti/p/@'.$city,
                    'contact_name' => '移住・関係人口相談窓口',
                    'contact_url' => 'https://example.com/'.$city.'/contact',
                    'is_published' => true,
                ]
            );

            $source = Source::updateOrCreate(
                ['url' => 'https://example.com/'.$city.'/events'],
                [
                    'municipality_id' => $municipality->id, 'page_type' => 'official',
                    'crawl_frequency' => 'weekly', 'priority' => 2, 'is_active' => true,
                    'robots_checked' => true, 'terms_checked' => true,
                    'terms_note' => '公開イベント情報のみ構造化。本文転載なし・一次情報へ送客。',
                    'last_crawled_at' => now()->subDays(3),
                ]
            );

            foreach ($activityTemplates as $ai => [$title, $catName, $desc, $child, $beginner, $lodging]) {
                if (($mi + $ai) % 2 === 0 && $ai > 2) {
                    continue; // 自治体ごとに件数を散らす
                }
                $fullTitle = "{$title}（{$municipality->city}）";
                $activity = Activity::updateOrCreate(
                    ['slug' => Str::slug($fullTitle) ?: 'a-'.$mi.'-'.$ai],
                    [
                        'municipality_id' => $municipality->id,
                        'category_id' => $categories[$catName] ?? null,
                        'title' => $fullTitle,
                        'summary' => $desc,
                        'description' => $desc."\n\n詳細・申込は一次情報をご確認ください。",
                        'source_url' => $source->url.'/'.$ai,
                        'apply_url' => $source->url.'/'.$ai.'/apply',
                        'organizer_name' => $municipality->name.' 地域づくり課',
                        'application_deadline' => now()->addDays(20 + $ai * 5),
                        'start_at' => now()->addDays(30 + $ai * 5)->setTime(10, 0),
                        'end_at' => now()->addDays(30 + $ai * 5)->setTime(15, 0),
                        'is_recurring' => $catName === 'ボランティア',
                        'fee_text' => $ai % 2 === 0 ? '無料（交通費自己負担）' : '2,000円',
                        'child_friendly' => $child,
                        'beginner_friendly' => $beginner,
                        'online_available' => str_contains($catName, '副業'),
                        'has_reward' => str_contains($catName, '副業'),
                        'transport_support' => $ai % 3 === 0,
                        'lodging_support' => $lodging,
                        'target_audience' => '地域に関心のある方',
                        'capacity' => 20,
                        'status' => 'published',
                        'verified_at' => now()->subDays($ai),
                        'extraction_confidence' => 0.7 + $ai * 0.05,
                    ]
                );
                if ($searchTags) {
                    $activity->tags()->syncWithoutDetaching(
                        collect($searchTags)->random(min(3, count($searchTags)))->all()
                    );
                }
            }

            // クロール・抽出履歴のデモ（ADM-06 抽出レビュー用）
            $crawl = CrawlRun::create([
                'source_id' => $source->id, 'http_status' => 200,
                'content_hash' => Str::random(40), 'charset' => 'utf-8',
                'content_type' => 'html', 'result' => 'changed', 'diff_status' => 'updated',
                'fetched_at' => now()->subDays(3),
            ]);
            ExtractionRun::create([
                'crawl_run_id' => $crawl->id, 'model' => 'demo-extractor', 'prompt_version' => 'v1',
                'source_text' => "{$municipality->name} のイベントページから取得した本文サンプル（デモ）。",
                'extracted' => [
                    'title' => '新着イベント候補',
                    'category' => '農業・食',
                    'application_deadline' => now()->addDays(40)->toDateString(),
                    'fee' => '無料',
                ],
                'confidence' => 0.82,
                'review_status' => 'pending',
            ]);
        }

        // 取得失敗サンプル（ADM-07 / CRW-013）
        $failSource = Source::first();
        if ($failSource) {
            CrawlRun::create([
                'source_id' => $failSource->id, 'http_status' => 404,
                'content_type' => 'html', 'result' => 'failed', 'diff_status' => 'none',
                'error_message' => 'Not Found（リンク切れの可能性）', 'fetched_at' => now()->subDay(),
            ]);
        }
    }
}
