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

### 公開情報収集・AI抽出ワークフロー（CRW / バッチ）
キュー駆動のパイプライン（設計：`docs/crawling-workflow.md`）。
- `locona:crawl`（JOB-001）→ **FetchSourceJob**（取得・SHA256差分検知・スナップショット保存）
  → 変更時のみ **ExtractActivityJob**（AI抽出・信頼度）→ `pending` → 人手確認（ADM-008）
- Fetcher（`null`/`http`）・Extractor（`heuristic`/`llm`）は `config/locona.php` で**差し替え可能**（CRW-008）
- 既定は安全側（実取得・外部送信なし）。`HeuristicExtractor` はJSON-LD/meta/日付/費用/条件をローカル解析
- 取得は規約・robots確認をゲート（CRW-004/005）。管理画面に取得履歴・手動再取得（CRW-002/014）
- 抽出の「採用」で活動ドラフトを自動生成（5-1 公開判定への橋渡し）
- `locona:detect-expired`（JOB-004）、スケジュールは `routes/console.php`

### その他の機能
- **公開API**（11-1）：`GET /api/activities`, `/api/activities/{id}`, `/api/municipalities`, `/{id}`
- **SEO**（NFR-011）：`/sitemap.xml`・`/robots.txt` 動的生成
- **一括操作・CSV入出力**（ADM-011/012）：公開制御の一括実行、自治体/活動CSV、自治体CSVインポート
- **重複候補検出**（ADM-009/CRW-011）：同一URL・類似タイトル・同一開催日
- **LINE Webhook受信**（LIN-003）：`POST /webhooks/line/{channel}`、HMAC-SHA256署名検証
- **修正・削除依頼**（ADM-014/SEC-012）：活動詳細から受付、運用側で対応管理
- **情報品質KPI**（14-1）：30日以内確認率・期限切れ残存率などをダッシュボード表示

> クローリングの実取得を伴う運用開始は、対象サイトごとの利用規約・robots.txt・アクセス頻度の確認
> （CRW-004/005、法務レビュー論点）の完了が前提です。本文・画像の転載は避け、事実情報と一次情報送客を基本とします。

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
