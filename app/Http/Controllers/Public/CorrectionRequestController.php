<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\CorrectionRequest;
use Illuminate\Http\Request;

/**
 * 利用者・主催者からの修正/削除依頼の受付（ADM-014 / SEC-012）。
 * 認証不要。誤掲載防止・削除依頼窓口として活動詳細から送信する。
 */
class CorrectionRequestController extends Controller
{
    public function store(Request $request, Activity $activity)
    {
        $data = $request->validate([
            'type' => ['required', 'in:correction,deletion,other'],
            'message' => ['required', 'string', 'max:2000'],
            'requester_name' => ['nullable', 'string', 'max:255'],
            'requester_email' => ['nullable', 'email', 'max:255'],
        ]);

        CorrectionRequest::create([
            ...$data,
            'activity_id' => $activity->id,
            'municipality_id' => $activity->municipality_id,
            'status' => 'open',
            'ip_address' => $request->ip(),
        ]);

        return back()->with('correction_sent', 'ご連絡ありがとうございます。内容を確認し対応いたします。');
    }
}
