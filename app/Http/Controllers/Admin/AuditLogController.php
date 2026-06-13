<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

/**
 * 監査ログ閲覧（ADM-015 / ADM-09 / SEC-007）。admin のみ。
 */
class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = AuditLog::with('user')
            ->when($request->input('action'), fn ($q, $a) => $q->where('action', $a))
            ->latest()
            ->paginate(50)->withQueryString();

        return view('admin.audit_logs.index', compact('logs'));
    }
}
