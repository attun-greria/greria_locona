<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Municipality;
use App\Models\Source;
use App\Support\Audit;
use Illuminate\Http\Request;

/**
 * 収集元URL管理（ADM-007 / CRW-001/004/005 / ADM-05）。
 * URL・自治体・種別・取得頻度・規約/robots確認・最終取得を管理する。
 */
class SourceController extends Controller
{
    public function index(Request $request)
    {
        $sources = Source::query()
            ->with(['municipality', 'crawlRuns' => fn ($q) => $q->latest('fetched_at')->limit(1)])
            ->when($request->input('municipality'), fn ($q, $m) => $q->where('municipality_id', $m))
            ->when($request->input('page_type'), fn ($q, $t) => $q->where('page_type', $t))
            ->orderBy('priority')->latest('updated_at')
            ->paginate(20)->withQueryString();

        $municipalities = Municipality::orderBy('name')->get();

        return view('admin.sources.index', compact('sources', 'municipalities'));
    }

    public function create()
    {
        return view('admin.sources.form', [
            'source' => new Source(),
            'municipalities' => Municipality::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $source = Source::create($data);
        Audit::log('created', $source, $data);

        return redirect()->route('admin.sources.index')->with('status', '収集元URLを登録しました。');
    }

    public function edit(Source $source)
    {
        return view('admin.sources.form', [
            'source' => $source,
            'municipalities' => Municipality::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Source $source)
    {
        $data = $this->validateData($request);
        $source->update($data);
        Audit::log('updated', $source, $data);

        return redirect()->route('admin.sources.index')->with('status', '収集元URLを更新しました。');
    }

    public function destroy(Source $source)
    {
        Audit::log('deleted', $source);
        $source->delete();

        return redirect()->route('admin.sources.index')->with('status', '収集元URLを削除しました。');
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'municipality_id' => ['nullable', 'exists:municipalities,id'],
            'url' => ['required', 'url', 'max:1024'],
            'page_type' => ['required', 'in:official,tourism,migration,pdf,sns,other'],
            'crawl_frequency' => ['required', 'in:daily,weekly,monthly,manual'],
            'priority' => ['required', 'integer', 'min:1', 'max:5'],
            'terms_note' => ['nullable', 'string'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $data['robots_checked'] = $request->boolean('robots_checked');
        $data['terms_checked'] = $request->boolean('terms_checked');

        return $data;
    }
}
