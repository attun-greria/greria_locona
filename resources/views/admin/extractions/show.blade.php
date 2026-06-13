@extends('layouts.admin')

@section('title', 'AI抽出レビュー #'.$extraction->id)

@section('content')
<div class="grid lg:grid-cols-2 gap-6">
    {{-- 原文（CRW-010 原文保持） --}}
    <div class="bg-white rounded-xl border border-stone-200 p-5">
        <h2 class="font-bold text-slate-700 text-sm mb-2">取得原文</h2>
        <div class="text-xs text-slate-400 mb-2">
            元URL：<a href="{{ $extraction->crawlRun?->source?->url }}" target="_blank" rel="nofollow" class="text-brand hover:underline">{{ $extraction->crawlRun?->source?->url }}</a>
        </div>
        <pre class="text-xs bg-stone-50 rounded-lg p-3 max-h-96 overflow-auto whitespace-pre-wrap">{{ $extraction->source_text ?: '（原文なし）' }}</pre>
    </div>

    {{-- AI抽出候補 --}}
    <div class="bg-white rounded-xl border border-stone-200 p-5">
        <h2 class="font-bold text-slate-700 text-sm mb-2">AI抽出候補</h2>
        <div class="text-xs text-slate-400 mb-2">モデル：{{ $extraction->model ?? '—' }} / プロンプト版：{{ $extraction->prompt_version ?? '—' }} / 信頼度：{{ !is_null($extraction->confidence) ? number_format($extraction->confidence*100).'%' : '—' }}</div>
        <dl class="text-sm space-y-2">
            @forelse (($extraction->extracted ?? []) as $key => $value)
                <div class="grid grid-cols-3 gap-2 border-b border-stone-50 pb-1">
                    <dt class="text-slate-500">{{ $key }}</dt>
                    <dd class="col-span-2 font-medium">{{ is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value }}</dd>
                </div>
            @empty
                <p class="text-slate-400">抽出データがありません。</p>
            @endforelse
        </dl>

        <form method="post" action="{{ route('admin.extractions.review', $extraction) }}" class="mt-5 flex gap-2">@csrf
            <button name="decision" value="approved" class="flex-1 py-2 rounded-lg bg-brand text-white font-bold">採用</button>
            <button name="decision" value="edited" class="flex-1 py-2 rounded-lg bg-amber-500 text-white font-bold">要修正</button>
            <button name="decision" value="rejected" class="flex-1 py-2 rounded-lg bg-stone-300 text-slate-700 font-bold">却下</button>
        </form>
        <a href="{{ route('admin.extractions.index') }}" class="block mt-3 text-sm text-slate-500 hover:underline">← 一覧に戻る</a>
    </div>
</div>
@endsection
