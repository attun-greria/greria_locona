# 自治体データ収集ワークフロー（設計）

要件定義書 5-1 / 8章（CRW）/ 11-2（JOB）/ NFR-005 に基づく、公開情報の
収集・構造化パイプラインの設計と実装。

## 方針

- 自治体サイトを複製しない。**公開された事実情報を抽出し、詳細は一次情報へ送客**（基本方針）。
- 完全自動化しない。**自動収集 + AI抽出 + 人による確認**（5-1）。抽出結果は必ずレビュー（ADM-008）を経て公開。
- 取得は**規約・robots.txt確認をゲート**にする（CRW-004/005、法務レビュー論点）。
- Webリクエストから分離した**非同期ジョブ**で実行（NFR-005）。
- Fetcher / Extractor は**差し替え可能**（CRW-008）。既定は安全側（実取得しない・外部送信しない）。

## 全体像

```
[収集元URL登録]            operator が URL/種別/頻度/規約・robots確認を登録（CRW-001/ADM-007）
      │
      ▼
locona:crawl  ── Scheduler（日次/週次）が起動（JOB-001）
      │  対象 = is_active かつ robots_checked かつ terms_checked
      ▼
FetchSourceJob ── 取得 + 差分検知（CRW-002/003/006）  ［キュー実行］
      │   ├─ Fetcher::fetch(url)            … http / null（差し替え）
      │   ├─ SHA-256 で前回ハッシュと比較     … new / updated / unchanged
      │   ├─ 変更時のみスナップショット保存    … Storage（DBに原文を入れない 10-3）
      │   ├─ crawl_runs に結果記録            … http_status/result/diff_status
      │   └─ 失敗時 failure_count++・再試行    … tries=LOCONA_MAX_RETRIES（CRW-013）
      │
      ▼（new / updated のみ）
ExtractActivityJob ── AI抽出（CRW-007/008/010）       ［キュー実行］
      │   ├─ Extractor::extract(snapshot, url)  … heuristic / llm / null（差し替え）
      │   └─ extraction_runs に保存             … model/prompt版/原文/抽出JSON/信頼度
      │                                           review_status = pending
      ▼
[抽出レビュー]   operator が原文とAI候補を比較し 採用/修正/却下（ADM-008）
      │
      ▼
[活動として公開]  確認済み情報を activities に登録し published（5-1 公開判定）
      │
      ▼
locona:detect-expired ── 締切・開催日経過・リンク切れを再確認（JOB-004/CRW-012）
```

## コンポーネント

| 種別 | クラス | 役割 |
| --- | --- | --- |
| 設定 | `config/locona.php` | Fetcher/Extractorのdriver、UA、頻度、再試行、保存先 |
| 取得契約 | `App\Services\Crawling\Contracts\Fetcher` | `fetch(url): FetchResult` |
| 取得実装 | `NullFetcher`（既定） / `HttpFetcher` | 取得しない / 実HTTP取得 |
| 抽出契約 | `App\Services\Extraction\Contracts\Extractor` | `extract(content,url): ExtractionResult` |
| 抽出実装 | `HeuristicExtractor`（既定） / `NullExtractor` | ローカル解析 / 無効（LLM差し替え枠） |
| ジョブ | `FetchSourceJob` | 取得・差分検知・スナップショット・抽出投入 |
| ジョブ | `ExtractActivityJob` | スナップショットからの抽出・履歴保存 |
| 起動 | `locona:crawl` | 対象ソース抽出 → ジョブ投入（`--sync`/`--source`） |
| 起動 | `locona:detect-expired` | 期限切れ検知（JOB-004） |
| 管理 | 収集元の「履歴」「今すぐ再取得」 | 取得履歴可視化（CRW-002）・手動再取得（CRW-014） |

## 差分検知（CRW-006）

`crawl_runs.content_hash` = 取得本文の SHA-256。`sources.last_content_hash` と比較し：

- 前回ハッシュ無し → `new`
- 一致 → `unchanged`（スナップショット保存・抽出をスキップ）
- 不一致 → `updated`

`new` / `updated` のときのみ抽出ジョブへ送る（無駄な再抽出・外部送信を避ける）。

## 信頼度（CRW-009）

`HeuristicExtractor` は JSON-LD(Event) を最優先し、拾えた項目数で信頼度を段階付け。
レビュー画面で信頼度の低い順に確認する運用に使う（人手確認の優先順位付け）。

## 有効化の手順（実運用）

1. 対象サイトごとに利用規約・robots.txt・アクセス頻度を確認し、収集元の
   「利用規約確認済み」「robots.txt確認済み」をON（CRW-005）。
2. `.env` で `LOCONA_FETCHER=http`、必要なら `LOCONA_EXTRACTOR=llm` と
   `AI_EXTRACTION_*` を設定（SEC-006/010：キーはenv、外部送信内容を制御）。
3. cron に `schedule:run` を登録（取得・期限切れ確認が定期実行される）。
4. キューワーカーで `FetchSourceJob` / `ExtractActivityJob` を処理
   （共有サーバーは `queue:work --stop-when-empty` をcron）。

> 既定（`null`/`heuristic`）のままでも、テスト用FetcherやサンプルデータでUI・確認フローは動作する。
> 実取得を伴う運用開始は、法務確認の完了が前提。
