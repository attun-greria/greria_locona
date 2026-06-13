<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExtractionRun;
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

    public function review(Request $request, ExtractionRun $extraction)
    {
        $request->validate([
            'decision' => ['required', 'in:approved,edited,rejected'],
        ]);

        $extraction->update([
            'review_status' => $request->input('decision'),
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);
        Audit::log('extraction_reviewed', $extraction, ['decision' => $request->input('decision')]);

        return redirect()->route('admin.extractions.index')
            ->with('status', '抽出結果のレビューを記録しました。');
    }
}
