<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * 監査ログ記録ヘルパー（ADM-015 / SEC-007）。
 * 管理画面の主要操作（作成・更新・削除・公開制御・CSV取込・権限変更）を記録する。
 */
class Audit
{
    public static function log(string $action, ?Model $target = null, array $changes = []): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'target_type' => $target ? class_basename($target) : null,
            'target_id' => $target?->getKey(),
            'changes' => $changes ?: null,
            'ip_address' => Request::ip(),
            'created_at' => now(),
        ]);
    }
}
