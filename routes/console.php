<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| バッチ・スケジュール（11-2 バッチ・ジョブ / NFR-005）
|--------------------------------------------------------------------------
| クロール・AI抽出・期限切れ確認・集計はWebリクエストから分離し非同期で実行する。
*/

// JOB-001: URL定期取得（日次）
Schedule::command('locona:crawl --frequency=daily')->dailyAt('03:00');
Schedule::command('locona:crawl --frequency=weekly')->weeklyOn(1, '03:30');

// JOB-004: 期限切れ確認（日次・既定はドライラン。運用担当が確認画面で対応）
Schedule::command('locona:detect-expired')->dailyAt('05:00');
