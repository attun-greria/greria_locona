@extends('layouts.admin')

@section('title', '収集元URL管理')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <form method="get" class="flex gap-2 text-sm">
            <select name="municipality" class="px-3 py-2 border border-stone-300 rounded-lg bg-white">
                <option value="">すべての自治体</option>
                @foreach ($municipalities as $m)
                    <option value="{{ $m->id }}" @selected(request('municipality')==$m->id)>{{ $m->name }}</option>
                @endforeach
            </select>
            <select name="page_type" class="px-3 py-2 border border-stone-300 rounded-lg bg-white">
                <option value="">すべての種別</option>
                @foreach (['official'=>'公式','tourism'=>'観光','migration'=>'移住','pdf'=>'PDF','sns'=>'SNS','other'=>'その他'] as $k=>$v)
                    <option value="{{ $k }}" @selected(request('page_type')===$k)>{{ $v }}</option>
                @endforeach
            </select>
            <button class="px-4 py-2 rounded-lg bg-brand text-white">絞り込み</button>
        </form>
        <a href="{{ route('admin.sources.create') }}" class="px-4 py-2 rounded-lg bg-brand text-white text-sm">＋ 収集元を追加</a>
    </div>

    <div class="bg-white rounded-xl border border-stone-200 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-slate-500 border-b border-stone-100">
                <tr><th class="px-4 py-2">URL</th><th class="px-4 py-2">自治体</th><th class="px-4 py-2">種別</th><th class="px-4 py-2">頻度</th><th class="px-4 py-2">規約/robots</th><th class="px-4 py-2">最終取得</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($sources as $s)
                    <tr class="border-b border-stone-50 hover:bg-stone-50">
                        <td class="px-4 py-3 max-w-xs truncate"><a href="{{ $s->url }}" target="_blank" rel="nofollow" class="text-brand hover:underline">{{ $s->url }}</a></td>
                        <td class="px-4 py-3 text-slate-500">{{ $s->municipality?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $s->page_type }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $s->crawl_frequency }}</td>
                        <td class="px-4 py-3">
                            <span class="{{ $s->terms_checked ? 'text-accent-dark' : 'text-slate-300' }}">規約</span>
                            <span class="{{ $s->robots_checked ? 'text-accent-dark' : 'text-slate-300' }}">robots</span>
                        </td>
                        <td class="px-4 py-3 text-slate-400">{{ optional($s->last_crawled_at)->format('y/n/j') ?? '未取得' }}</td>
                        <td class="px-4 py-3 text-right"><a href="{{ route('admin.sources.edit', $s) }}" class="text-brand hover:underline">編集</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-10 text-center text-slate-400">収集元URLがありません。</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $sources->links() }}</div>
@endsection
