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
 * 種別（活動・イベント / 制度・支援 / 相談・紹介）を取り混ぜた具体的なデモコンテンツ。
 * ※ 内容はデモ用に作成したもので、実在の募集・制度ではありません。
 */
class SampleContentSeeder extends Seeder
{
    private array $categories = [];

    private array $searchTags = [];

    public function run(): void
    {
        $this->categories = ActivityCategory::pluck('id', 'name')->all();
        $this->searchTags = Tag::where('type', 'search')->pluck('id')->all();

        $municipalities = [
            ['長野県飯山市', '長野県', '飯山市', '雪と里山に恵まれた北信州のまち。棚田や森林、スキー、農山村体験、二地域居住の受け入れに力を入れています。', '雪と里山、よりみちの北信州'],
            ['島根県海士町', '島根県', '海士町', '隠岐諸島の離島。半農半X、高校魅力化、Iターン受け入れで知られ、「ないものはない」を掲げて挑戦を続ける地域です。', 'ないものはない、半農半Xの島'],
            ['徳島県神山町', '徳島県', '神山町', 'サテライトオフィスやアート、神山まるごと高専など、創造的な人の流れで関係人口づくりを進める山あいのまち。', '創造的過疎、アートと働くまち'],
            ['北海道下川町', '北海道', '下川町', '森林資源を活かした循環型・SDGsのまちづくり。林業、トドマツ精油、アイスキャンドルなど森と生きる暮らしが息づきます。', '森と生きる、SDGsのまち'],
        ];

        $cityEvents = $this->cityEvents();

        foreach ($municipalities as $mi => [$name, $pref, $city, $summary, $catchphrase]) {
            $municipality = Municipality::updateOrCreate(
                ['slug' => Str::slug($name) ?: 'm-'.$mi],
                [
                    'name' => $name, 'prefecture' => $pref, 'city' => $city,
                    'summary' => $summary, 'catchphrase' => $catchphrase,
                    'official_url' => 'https://example.com/'.$city,
                    'related_urls' => ['https://example.com/'.$city.'/kanko', 'https://example.com/'.$city.'/iju'],
                    'line_url' => 'https://line.me/R/ti/p/@'.$city,
                    'contact_name' => $name.' 移住・定住相談窓口',
                    'contact_url' => 'https://example.com/'.$city.'/iju/soudan',
                    'is_published' => true,
                ]
            );

            $source = Source::updateOrCreate(
                ['url' => 'https://example.com/'.$city.'/events'],
                [
                    'municipality_id' => $municipality->id, 'page_type' => 'official',
                    'license_tier' => 'open', 'publication_policy' => 'publishable',
                    'attribution_name' => $name.'公式サイト',
                    'license_url' => 'https://www.digital.go.jp/resources/open_data/public_data_license_v1.0',
                    'crawl_frequency' => 'weekly', 'priority' => 2, 'is_active' => true,
                    'robots_checked' => true, 'terms_checked' => true,
                    'terms_note' => '政府標準利用規約準拠（出典明示で利用可）。本文・画像の転載なし、一次情報へ送客。',
                    'last_crawled_at' => now()->subDays(3),
                ]
            );

            // 民間・SNS由来は社内診断のみ（非公開）の例
            $restricted = Source::updateOrCreate(
                ['url' => 'https://example.com/'.$city.'/private-events'],
                [
                    'municipality_id' => $municipality->id, 'page_type' => 'sns',
                    'license_tier' => 'restricted', 'publication_policy' => 'internal_only',
                    'attribution_name' => '民間イベント情報（参考）',
                    'crawl_frequency' => 'manual', 'priority' => 4, 'is_active' => true,
                    'robots_checked' => true, 'terms_checked' => true,
                    'terms_note' => '規約上の転載不可。診断・社内インデックス用途のみ（公開しない）。',
                    'last_crawled_at' => now()->subDays(5),
                ]
            );

            // 1) 地域固有のイベント
            foreach ($cityEvents[$city] as $idx => $item) {
                $this->createActivity($municipality, $source, "ev-{$mi}-{$idx}", $item);
            }
            // 2) 共通の制度・支援（常設）
            foreach ($this->programs($name) as $idx => $item) {
                $this->createActivity($municipality, $source, "pg-{$mi}-{$idx}", $item);
            }
            // 3) 共通の相談・紹介（常設）
            foreach ($this->intros($name) as $idx => $item) {
                $this->createActivity($municipality, $source, "in-{$mi}-{$idx}", $item);
            }

            // 4) 社内診断のみ（民間由来・非公開）の例。公開サイトには表示されない。
            $this->createActivity($municipality, $restricted, "di-{$mi}", [
                'kind' => 'event', 'category' => '観光・体験',
                'title' => '民間主催 まちなかマルシェ（参考）',
                'summary' => '民間イベントサイトで告知されている地域イベント。規約上の転載不可のため社内診断用の参考データとして保持。',
                'description' => "公開はせず、自治体の発信状況の診断・営業材料として活用します（送客や編集記事化は許諾取得後に検討）。",
            ]);

            $this->seedCrawlDemo($municipality, $source);
        }

        // 取得失敗サンプル（ADM-07 / CRW-013）
        if ($failSource = Source::first()) {
            CrawlRun::create([
                'source_id' => $failSource->id, 'http_status' => 404,
                'content_type' => 'html', 'result' => 'failed', 'diff_status' => 'none',
                'error_message' => 'Not Found（リンク切れの可能性）', 'fetched_at' => now()->subDay(),
            ]);
        }
    }

    /** 地域固有のイベント（具体的内容） */
    private function cityEvents(): array
    {
        return [
            '飯山市' => [
                ['category' => '農業・食', 'title' => '棚田オーナーになって米づくり', 'summary' => '北信州の棚田で、田植えから稲刈りまで一年を通して米づくりを体験。収穫したお米はオーナー特典としてお届けします。', 'description' => "地元農家が一年を通してサポート。春の田植え、夏の草取り、秋の稲刈りと、季節ごとに棚田に通って米づくりを体験できます。\n収穫した新米（約20kg）はオーナー特典としてご自宅へお届け。週末開催・初心者歓迎、家族での参加も大歓迎です。", 'fee_text' => '1区画 年間30,000円（収穫米約20kg付）', 'child' => true, 'beginner' => true, 'quote' => '棚田オーナー制度では、田植え・稲刈り等の農作業体験を通じて、棚田の保全と地域との交流を図ります。', 'quote_source' => '飯山市公式サイト「棚田オーナー制度のご案内」'],
                ['category' => '自然・アウトドア', 'title' => 'ブナ林スノーシューハイク', 'summary' => '雪に包まれた鍋倉高原のブナ林を、ガイドと一緒にスノーシューで歩く半日ツアー。', 'description' => "豪雪地ならではの真っ白なブナ原生林を、専門ガイドの案内でのんびり散策。動物の足跡や冬芽を観察しながら、雪上ランチも楽しめます。スノーシュー・ストックはレンタル込み。", 'fee_text' => '3,500円（スノーシューレンタル・保険込）', 'beginner' => true],
                ['category' => '空き家・二地域居住', 'title' => '古民家ゲストハウスで二地域居住お試し滞在', 'summary' => '改修した古民家に滞在し、平日はリモートワーク、週末は地域活動という二地域居住をお試しできます。', 'description' => "築90年の古民家を改修したゲストハウスに最大2週間滞在。高速Wi-Fi完備でワーケーションが可能です。地域住民との交流会や空き家バンク物件の見学もアレンジします。", 'fee_text' => '1泊2,000円（光熱費・Wi-Fi込）', 'lodging' => true],
                ['category' => '子育て・教育', 'title' => '親子で雪国の暮らし自然体験', 'summary' => 'かまくらづくりや雪遊び、地元食材の郷土食づくりを親子で楽しむ日帰りプログラム。', 'description' => "雪国の知恵を遊びながら学ぶ親子向け体験。午前はかまくらづくりと雪遊び、午後はおやき・笹寿司などの郷土食づくり。未就学児から参加できます。", 'fee_text' => '無料（材料費は当日500円）', 'child' => true, 'beginner' => true],
            ],
            '海士町' => [
                ['category' => '農業・食', 'title' => '隠岐牛と岩がきの島ごはん体験', 'summary' => 'ブランド「隠岐牛」や名産の岩がきなど、島の海と大地の幸を味わい・学ぶ食体験。', 'description' => "生産者を訪ねて隠岐牛の飼育や岩がきの養殖について学んだあと、島の食材を使った昼食を味わいます。食を入り口に島の暮らしと産業に触れられます。", 'fee_text' => '4,000円（昼食付）', 'beginner' => true],
                ['category' => '空き家・二地域居住', 'title' => '半農半X 暮らし体験ステイ', 'summary' => '午前は畑仕事、午後は自分の仕事や活動。島の「半農半X」な暮らしを数日間お試し。', 'description' => "Iターンの先輩の家に滞在しながら、畑仕事と地域の仕事を組み合わせた島の暮らしを体験。住まい・仕事・コミュニティのリアルを知ることができます。", 'fee_text' => '3泊5,000円（朝食・宿泊込）', 'lodging' => true],
                ['category' => 'ボランティア', 'title' => '高校魅力化プロジェクト 学習サポーター', 'summary' => '島前高校の生徒の学びを支える継続ボランティア。オンライン参加も可能です。', 'description' => "全国から生徒が集まる島前高校で、放課後の学習サポートや探究活動の伴走を行います。教員免許は不要。月数回・オンライン併用で継続的に関われます。", 'fee_text' => '無料（交通費・宿泊の支援あり）', 'online' => true, 'recurring' => true, 'transport' => true],
                ['category' => '副業・プロボノ', 'title' => '離島の地域づくり 副業人材募集', 'summary' => '島の特産品のブランディングやWeb発信を、リモート中心で支援する副業人材を募集。', 'description' => "海士町の事業者と一緒に、商品開発・販路拡大・情報発信に取り組む副業プロジェクト。月10時間程度・リモート中心。年数回の現地訪問費は支給します。", 'fee_text' => '報酬あり（業務委託・応相談）', 'online' => true, 'reward' => true, 'transport' => true],
            ],
            '神山町' => [
                ['category' => '農業・食', 'title' => 'すだち収穫と加工体験', 'summary' => '神山名産のすだちを収穫し、ポン酢やスイーツづくりに挑戦する秋の人気プログラム。', 'description' => "農家のすだち畑で収穫を体験し、搾汁してポン酢やすだちサイダーづくりに挑戦。お土産付きで、親子での参加にもおすすめです。", 'fee_text' => '2,500円（お土産・加工材料込）', 'child' => true, 'beginner' => true],
                ['category' => '副業・プロボノ', 'title' => 'サテライトオフィスお試し勤務ツアー', 'summary' => '古民家サテライトオフィスで実際に働きながら、神山での移住・二拠点を検討できる2泊3日。', 'description' => "光ファイバーが整う神山のサテライトオフィスで、普段の仕事をしながら地域の起業家やクリエイターと交流。働き方と暮らしの両面から移住を検討できます。", 'fee_text' => '無料（宿泊費の補助あり）', 'online' => true, 'lodging' => true],
                ['category' => '文化・祭り', 'title' => 'アート・イン・レジデンス 滞在制作', 'summary' => '国内外の作家が滞在制作する神山で、制作・展示に関わるアーティストを募集。', 'description' => "「神山アーティスト・イン・レジデンス」の枠組みで、町に滞在しながら作品を制作・展示。地域の素材や風景、人との対話から生まれる表現を支援します。", 'fee_text' => '滞在支援あり（応相談）', 'lodging' => true],
                ['category' => '子育て・教育', 'title' => '神山まるごと高専 オープンキャンパス', 'summary' => 'テクノロジー×デザインで起業家を育てる高専の見学会。保護者・中学生向け。', 'description' => "2023年に開校した私立高専のキャンパスを見学し、起業家精神を育むカリキュラムや寮生活を紹介。中学生とその保護者を対象にしたプログラムです。", 'fee_text' => '無料', 'beginner' => true],
            ],
            '下川町' => [
                ['category' => '農業・食', 'title' => 'トドマツ精油づくりワークショップ', 'summary' => '森林整備で出るトドマツの枝葉から、香り高いエッセンシャルオイルを蒸留する体験。', 'description' => "森の恵みを余すことなく使う下川町の循環の取り組みを学びながら、トドマツの枝葉を蒸留して精油づくりに挑戦。作った精油はお持ち帰りいただけます。", 'fee_text' => '2,000円（精油お土産付）', 'child' => true, 'beginner' => true],
                ['category' => '自然・アウトドア', 'title' => '森林ガイドと歩く原生林トレッキング', 'summary' => '町有林を管理する森林ガイドと一緒に、四季折々の森を歩く半日トレッキング。', 'description' => "FSC認証を受けた持続可能な森づくりの現場を、ガイドの解説とともに歩きます。野鳥や植物を観察しながら、森林と暮らしのつながりを体感できます。", 'fee_text' => '3,000円（ガイド・保険込）', 'beginner' => true],
                ['category' => 'ボランティア', 'title' => 'アイスキャンドルミュージアム 運営ボランティア', 'summary' => '冬の風物詩アイスキャンドルづくり・点灯を支える継続ボランティアを募集。', 'description' => "数千個のアイスキャンドルが灯る冬のイベントを、制作から点灯・片付けまで支えるボランティア。地域内外から参加でき、温かい交流が生まれます。", 'fee_text' => '無料', 'recurring' => true],
                ['category' => '副業・プロボノ', 'title' => '林業の仕事 体験ツアー', 'summary' => '植林から加工まで一貫する下川の林業の現場を体験し、移住・就業を検討できるツアー。', 'description' => "苗木づくり・植林・伐採・木材加工まで、町の基幹産業である林業の一連の流れを2泊3日で体験。就業や地域おこし協力隊への応募を検討する方におすすめです。", 'fee_text' => '無料（宿泊・現地交通の支援あり）', 'lodging' => true, 'transport' => true],
            ],
        ];
    }

    /** 共通の制度・支援（kind=program / 常設・締切なし） */
    private function programs(string $name): array
    {
        return [
            ['kind' => 'program', 'category' => '農業・食', 'title' => "ふるさと納税で{$name}を応援", 'summary' => "返礼品を通じて{$name}の特産品を楽しみながら、子育て・環境・地域づくりを応援できます。", 'description' => "{$name}へのふるさと納税。寄付の使い道は「子育て支援」「自然環境の保全」「関係人口づくり」などから選べます。寄付はオンラインで完結し、地域の特産品が返礼品として届きます。", 'fee_text' => '寄付額に応じた返礼品あり（自己負担2,000円）'],
            ['kind' => 'program', 'category' => '副業・プロボノ', 'title' => "{$name} 地域おこし協力隊 募集", 'summary' => '最長3年、地域に暮らしながら活動。月額報酬・住居・活動費の支援があり、任期後の定住・起業も支援します。', 'description' => "{$name}で地域おこし協力隊として活動するメンバーを通年で募集。活動テーマは農林業、観光、情報発信、移住支援など。月額報酬に加え、住居・活動経費の支援、任期後の起業・定住に向けた伴走支援があります。", 'fee_text' => '月額報酬あり＋住居・活動費支援', 'reward' => true, 'lodging' => true, 'transport' => true],
            ['kind' => 'program', 'category' => '空き家・二地域居住', 'title' => "{$name} 移住支援金（最大100万円）", 'summary' => '東京圏から移住し対象企業へ就業または起業した世帯に、最大100万円（単身60万円）を支給します。', 'description' => "東京23区在住・通勤の方が{$name}へ移住し、マッチングサイト掲載企業への就業や起業等の要件を満たす場合に支給される国・自治体の制度です。世帯100万円・単身60万円が基本で、18歳未満の帯同で加算があります。", 'fee_text' => '支給：世帯100万円／単身60万円'],
            ['kind' => 'program', 'category' => '空き家・二地域居住', 'title' => "{$name} 空き家バンク", 'summary' => '町内の空き家・空き地の登録物件を紹介。改修補助制度とあわせて住まい探しを支援します。', 'description' => "{$name}内の売却・賃貸可能な空き家を登録・公開する制度です。現地見学のアレンジや所有者との橋渡し、改修費の補助制度の案内まで、住まい探しをワンストップで支援します。", 'fee_text' => '登録・利用無料（改修補助制度あり）'],
        ];
    }

    /** 共通の相談・紹介（kind=intro / 常設） */
    private function intros(string $name): array
    {
        return [
            ['kind' => 'intro', 'category' => '空き家・二地域居住', 'title' => "{$name} オンライン移住相談（予約制・無料）", 'summary' => '暮らし・仕事・住まいの疑問に、移住の先輩や担当者がオンラインで個別にお答えします。', 'description' => "「いきなり移住は不安」という方に向けた、予約制・無料のオンライン個別相談です。仕事の探し方、住まい、子育て・教育環境、地域の雰囲気など、何でもご相談ください。二地域居住・お試し移住のご案内も可能です。", 'fee_text' => '無料（オンライン・予約制）', 'online' => true],
            ['kind' => 'intro', 'category' => '文化・祭り', 'title' => "関係人口コミュニティ「{$name}ファン」", 'summary' => '地域を応援するゆるやかなコミュニティ。LINEで活動案内やイベント情報を受け取れます。', 'description' => "{$name}に関わりたい人がゆるくつながるオンラインコミュニティです。LINE公式アカウントの友だち追加で、イベントや募集の最新情報、現地の様子が届きます。遠方からの参加も大歓迎で、できる範囲での関わりからはじめられます。", 'fee_text' => '無料（LINE友だち追加）', 'online' => true],
        ];
    }

    private function createActivity(Municipality $municipality, Source $source, string $seed, array $item): void
    {
        $defaults = [
            'kind' => 'event', 'category' => '観光・体験', 'description' => null, 'fee_text' => null,
            'child' => false, 'beginner' => false, 'online' => false, 'reward' => false,
            'transport' => false, 'lodging' => false, 'recurring' => false, 'capacity' => null,
            'visibility' => null, 'quote' => null, 'quote_source' => null,
        ];
        $d = array_merge($defaults, $item);
        $isEvent = $d['kind'] === 'event';
        $visibility = $d['visibility'] ?? $source->defaultVisibility();

        $fullTitle = "{$d['title']}（{$municipality->city}）";
        $hash = abs(crc32($seed));

        // イベントのみ日程・締切を持つ（制度・相談は常設）
        $deadline = null;
        $startAt = null;
        $endAt = null;
        if ($isEvent && ! $d['recurring']) {
            $deadlineDays = 10 + ($hash % 45);
            $deadline = now()->addDays($deadlineDays);
            $startAt = now()->addDays($deadlineDays + 7)->setTime(10, 0);
            $endAt = (clone $startAt)->setTime(15, 0);
        }

        $activity = Activity::updateOrCreate(
            ['slug' => Str::slug($fullTitle) ?: 'a-'.$seed],
            [
                'municipality_id' => $municipality->id,
                'category_id' => $this->categories[$d['category']] ?? null,
                'kind' => $d['kind'],
                'title' => $fullTitle,
                'summary' => $d['summary'],
                'description' => $d['description'],
                'source_url' => $source->url.'/'.$seed,
                'attribution_name' => $source->attribution_name ?: $municipality->name.'公式サイト',
                'cited_at' => now()->subDays($hash % 20)->toDateString(),
                'visibility' => $visibility,
                'quote_text' => $d['quote'],
                'quote_source' => $d['quote_source'],
                'apply_url' => $source->url.'/'.$seed.'/apply',
                'organizer_name' => $municipality->name.'（'.($isEvent ? '地域づくり課' : '移住定住推進室').'）',
                'application_deadline' => $deadline,
                'start_at' => $startAt,
                'end_at' => $endAt,
                'is_recurring' => $d['recurring'],
                'fee_text' => $d['fee_text'],
                'child_friendly' => $d['child'],
                'beginner_friendly' => $d['beginner'],
                'online_available' => $d['online'],
                'has_reward' => $d['reward'],
                'transport_support' => $d['transport'],
                'lodging_support' => $d['lodging'],
                'target_audience' => '地域に関心のある方・移住を検討する方',
                'capacity' => $isEvent ? (15 + ($hash % 20)) : null,
                'status' => 'published',
                'verified_at' => now()->subDays($hash % 25),
                'extraction_confidence' => round(0.6 + ($hash % 35) / 100, 2),
            ]
        );

        if ($this->searchTags) {
            $activity->tags()->syncWithoutDetaching(
                collect($this->searchTags)->random(min(3, count($this->searchTags)))->all()
            );
        }
    }

    /** クロール・抽出履歴のデモ（ADM-06 抽出レビュー用） */
    private function seedCrawlDemo(Municipality $municipality, Source $source): void
    {
        $crawl = CrawlRun::create([
            'source_id' => $source->id, 'http_status' => 200,
            'content_hash' => Str::random(40), 'charset' => 'utf-8',
            'content_type' => 'html', 'result' => 'changed', 'diff_status' => 'updated',
            'fetched_at' => now()->subDays(3),
        ]);
        ExtractionRun::create([
            'crawl_run_id' => $crawl->id, 'model' => 'heuristic-v1', 'prompt_version' => 'v1',
            'source_text' => "{$municipality->name}のイベントページから取得した本文サンプル（デモ）。新着の体験プログラム告知を含む。",
            'extracted' => [
                'title' => '新着：里山くらし体験ツアー',
                'category' => '農業・食',
                'application_deadline' => now()->addDays(40)->toDateString(),
                'fee_text' => '3,000円',
                'child_friendly' => true,
            ],
            'confidence' => 0.82,
            'review_status' => 'pending',
        ]);
    }
}
