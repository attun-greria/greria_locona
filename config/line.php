<?php

/*
|--------------------------------------------------------------------------
| LINE Messaging API 設定（9章 LIN / SEC-006/009）
|--------------------------------------------------------------------------
| チャネルシークレットは平文をソースに置かず env / シークレットストアで管理する。
| channels はチャネルID（line_channels.id）をキーに署名検証用シークレットを解決する。
*/

return [

    // 署名検証用シークレットのフォールバック（単一チャネル運用時）
    'default_secret' => env('LINE_CHANNEL_SECRET'),

    // 複数自治体チャネル運用時は channels に id => ['secret' => ...] を定義
    'channels' => [
        // 1 => ['secret' => env('LINE_CHANNEL_SECRET_1')],
    ],

    'access_token' => env('LINE_CHANNEL_ACCESS_TOKEN'),

];
