<?php

/*
|--------------------------------------------------------------------------
| LOCONA 収集・抽出ワークフロー設定（8章 CRW / 11-3 外部連携）
|--------------------------------------------------------------------------
| Fetcher（取得）とExtractor（AI抽出）は差し替え可能（CRW-008）。
| 既定は安全側：外部取得は 'null'（実取得しない）、抽出はローカルの 'heuristic'。
| 実運用では規約・robots確認のうえ 'http' / 'llm' を有効化する。
*/

return [

    'crawl' => [
        // null = 実取得しない（既定・安全） / http = 実HTTP取得
        'fetcher' => env('LOCONA_FETCHER', 'null'),

        // クローラのUser-Agent（CRW-004）
        'user_agent' => env('LOCONA_USER_AGENT', 'LOCONABot/0.1 (+https://locona.example.com/about/bot)'),

        // 取得タイムアウト（秒）
        'timeout' => (int) env('LOCONA_FETCH_TIMEOUT', 20),

        // 同一サイトへの最小取得間隔（秒）。アクセス負荷配慮（CRW-004）
        'min_interval' => (int) env('LOCONA_MIN_INTERVAL', 5),

        // 失敗時の再試行回数（CRW-013）
        'max_retries' => (int) env('LOCONA_MAX_RETRIES', 3),

        // スナップショット保存先ディスク（10-3: 原文はDBに入れずストレージへ）
        'snapshot_disk' => env('LOCONA_SNAPSHOT_DISK', 'local'),
    ],

    'extraction' => [
        // heuristic = ローカル解析（外部送信なし・既定） / llm = 外部LLM / null = 無効
        'driver' => env('LOCONA_EXTRACTOR', 'heuristic'),

        // 抽出モデル名・プロンプト版（CRW-010 履歴に保持）
        'model' => env('LOCONA_EXTRACTOR_MODEL', 'heuristic-v1'),
        'prompt_version' => env('LOCONA_PROMPT_VERSION', 'v1'),

        // LLM利用時のみ（SEC-006/010: キーはenvで管理し外部送信内容を制御）
        'llm_api_key' => env('AI_EXTRACTION_API_KEY'),
        'llm_endpoint' => env('AI_EXTRACTION_ENDPOINT'),
    ],

];
