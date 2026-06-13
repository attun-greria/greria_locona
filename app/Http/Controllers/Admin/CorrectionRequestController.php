<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CorrectionRequest;
use App\Support\Audit;
use Illuminate\Http\Request;

/**
 * 修正・削除依頼の管理（ADM-014 / SEC-012）。
 */
class CorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        $requests = CorrectionRequest::with(['activity', 'municipality'])
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(20)->withQueryString();

        $openCount = CorrectionRequest::whereIn('status', ['open', 'in_progress'])->count();

        return view('admin.corrections.index', compact('requests', 'openCount'));
    }

    public function update(Request $request, CorrectionRequest $correction)
    {
        $data = $request->validate([
            'status' => ['required', 'in:open,in_progress,resolved,rejected'],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $correction->update($data);
        Audit::log('correction_updated', $correction, $data);

        return back()->with('status', '依頼の対応状況を更新しました。');
    }
}
