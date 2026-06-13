# LOCONA

地域への「よりみち」を見つけ、つながりを育てる。
関係人口データ基盤・自治体向けLINE運用支援サービス（MVP）。

要件定義書「LOCONA システム要件定義書（Laravel / MySQL 版）Version 0.9」に基づく実装です。

## 技術スタック

| 項目 | 採用 |
| --- | --- |
| バックエンド | Laravel 13 / PHP 8.4 |
| データベース | MySQL 8.x（utf8mb4）。ローカルはsqliteで即起動可 |
| フロントエンド | Laravel Blade + Tailwind（CDN） |
| 非同期処理 | Laravel Queue / Scheduler（JOB-001〜006） |

## セットアップ

```bash
composer install
cp .env.example .env          # 既存の .env がなければ
php artisan key:generate
touch database/database.sqlite # sqliteで動かす場合
php artisan migrate --seed
php artisan serve
```

公開サイト: <http://127.0.0.1:8000/>
管理画面: <http://127.0.0.1:8000/admin/login>

### 初期ログイン（seed）

| ロール | メール | パスワード |
| --- | --- | --- |
| admin | admin@locona.test | password |
| operator | operator@locona.test | password |

> 本番では必ず初期パスワードを変更してください（SEC-002）。

### MySQLで動かす場合

`.env` の `DB_CONNECTION=mysql` を有効にし、接続情報を設定します。
文字コードは utf8mb4 を前提としています（10-3）。

## 実装済み機能（MVP / Must中心）

### 利用者向けWeb（PUB）
- トップページ：テーマ別入口・注目/最新活動・自治体入口（PUB-001）
- 活動一覧・検索：キーワード/地域/カテゴリ/時期/参加条件で絞り込み（PUB-002/003/004）
- 活動詳細：共通項目・関連活動・SEO/OGP/構造化データ（PUB-005/008/010/011）
- 一次情報への送客＋クリック計測（PUB-006）
- 自治体ページ：概要・関連リンク・活動一覧・LINE導線・相談窓口（PUB-007）
- 期限切れ制御（PUB-009）／検索結果なし（PUB-05）

### 運用管理画面（ADM）
- ログイン・ロール（admin/operator/editor）（ADM-001/002）
- ダッシュボード：公開数・確認待ち・期限切れ・取得失敗・クリック（ADM-013）
- 自治体管理（ADM-003）／活動管理：CRUD・複製・公開状態・改訂履歴（ADM-004/005）
- カテゴリ・タグ管理（ADM-006）／収集元URL管理（ADM-007）
- AI抽出レビュー：原文とAI候補の比較・採用/修正/却下（ADM-008）
- 期限切れ・取得失敗の確認（ADM-010）
- 監査ログ（admin限定）（ADM-015 / SEC-007）

### 公開情報収集・AI抽出（CRW / バッチ）
- 収集元URL・取得履歴・AI抽出履歴のデータ構造（CRW-001/002/008/010）
- `php artisan locona:crawl`：取得対象抽出＋ジョブ投入のスケルトン（JOB-001）
- `php artisan locona:detect-expired`：期限切れ検知（JOB-004）
- スケジュール登録は `routes/console.php`

> クローリング本体は、対象サイトごとの利用規約・robots.txt・アクセス頻度の確認（CRW-004/005）を
> 前提に `App\Jobs\*` として実装します。本文・画像の転載は避け、事実情報と一次情報送客を基本とします。

## データモデル（付録B）

`municipalities` / `activities` / `activity_categories` / `tags`(+`activity_tag`) /
`sources` / `crawl_runs` / `extraction_runs` / `activity_revisions` /
`audit_logs` / `line_channels` / `line_users` / `outbound_clicks`

## ディレクトリ

```
app/Http/Controllers/Public  利用者向けWeb
app/Http/Controllers/Admin   運用管理画面
app/Console/Commands         バッチ（クロール・期限切れ検知）
app/Support/Audit.php        監査ログ記録
database/migrations          スキーマ（2026_06_13_*）
database/seeders             初期ユーザー・カテゴリ/タグ・サンプル
resources/views/public       公開画面
resources/views/admin        管理画面
```

## 今後の拡張（Should / Future）

LINE Webhook受信・セグメント配信、自治体セルフ編集、重複判定、CSV入出力、
おすすめ診断・会員機能、全文検索エンジン、月次レポート自動生成 など（要件定義 16-3）。

---
&copy; 株式会社Greria
