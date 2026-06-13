@extends('layouts.admin')

@section('title', 'AI抽出レビュー')

@section('content')
    <form method="get" class="mb-4 flex gap-2 text-sm">
        @foreach (['pending'=>'確認待ち','approved'=>'採用','edited'=>'修正','rejected'=>'却下'] as $k=>$v)
            <a href="{{ route('admin.extractions.index', ['status'=>$k]) }}"
               class="px-3 py-1.5 rounded-lg {{ request('status','pending')===$k ? 'bg-brand text-white' : 'bg-white border border-stone-300' }}">{{ $v }}</a>
        @endforeach
    </form>

    <div class="bg-white rounded-xl border border-stone-200 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-slate-500 border-b border-stone-100">
                <tr><th class="px-4 py-2">抽出ID</th><th class="px-4 py-2">収集元</th><th class="px-4 py-2">モデル</th><th class="px-4 py-2">信頼度</th><th class="px-4 py-2">状態</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($runs as $r)
                    <tr class="border-b border-stone-50 hover:bg-stone-50">
                        <td class="px-4 py-3">#{{ $r->id }}</td>
                        <td class="px-4 py-3 text-slate-500 max-w-xs truncate">{{ $r->crawlRun?->source?->municipality?->name }} / {{ $r->crawlRun?->source?->url }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $r->model ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @if (!is_null($r->confidence))
                                <span class="px-2 py-0.5 rounded text-xs {{ $r->confidence >= 0.8 ? 'bg-accent/20 text-accent-dark' : 'bg-amber-100 text-amber-700' }}">{{ number_format($r->confidence*100) }}%</span>
                            @else — @endif
                        </td>
                        <td class="px-4 py-3 text-slate-500">{{ $r->review_status }}</td>
                        <td class="px-4 py-3 text-right"><a href="{{ route('admin.extractions.show', $r) }}" class="text-brand hover:underline">確認する</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">該当する抽出結果はありません。</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $runs->links() }}</div>
@endsection
