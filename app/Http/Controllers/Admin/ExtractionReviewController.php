<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExtractionRun;
use App\Services\Extraction\ActivityDraftFactory;
use App\Support\Audit;
use Illuminate\Http\Request;

/**
 * AI抽出結果レビュー（ADM-008 / CRW-008 / ADM-06）。
 * 取得原文とAI抽出候補を比較し、採用・修正・却下する。
 */
class ExtractionReviewController extends Controller
{
    public function index(Request $request)
    {
        $runs = ExtractionRun::with(['crawlRun.source.municipality', 'activity'])
            ->when($request->input('status', 'pending'), fn ($q, $s) => $q->where('review_status', $s))
            ->latest()
            ->paginate(20)->withQueryString();

        return view('admin.extractions.index', compact('runs'));
    }

    public function show(ExtractionRun $extraction)
    {
        $extraction->load(['crawlRun.source.municipality', 'activity']);

        return view('admin.extractions.show', compact('extraction'));
    }

    public function review(Request $request, ExtractionRun $extraction, ActivityDraftFactory $factory)
    {
        $request->validate([
            'decision' => ['required', 'in:approved,edited,rejected'],
        ]);
        $decision = $request->input('decision');

        $extraction->update([
            'review_status' => $decision,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);
        Audit::log('extraction_reviewed', $extraction, ['decision' => $decision]);

        // 採用時：未連携なら活動ドラフトを自動生成し、編集画面へ誘導（5-1 公開判定への橋渡し）
        if ($decision === 'approved' && ! $extraction->activity_id) {
            $activity = $factory->fromExtraction($extraction);

            if ($activity) {
                $extraction->update(['activity_id' => $activity->id]);
                Audit::log('activity_drafted_from_extraction', $activity, ['extraction_id' => $extraction->id]);

                return redirect()->route('admin.activities.edit', $activity)
                    ->with('status', '抽出結果から活動ドラフトを作成しました。内容を確認して公開してください。');
            }

            return redirect()->route('admin.extractions.index')
                ->with('status', '採用しました。収集元に自治体が未設定のため、活動は手動で作成してください。');
        }

        return redirect()->route('admin.extractions.index')
            ->with('status', '抽出結果のレビューを記録しました。');
    }
}
