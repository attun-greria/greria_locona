@extends('layouts.admin')

@section('title', '取得履歴')

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <div>
            <div class="text-sm text-slate-500">{{ $source->municipality?->name ?? '自治体未設定' }}</div>
            <a href="{{ $source->url }}" target="_blank" rel="nofollow" class="text-brand hover:underline break-all">{{ $source->url }}</a>
        </div>
        <div class="flex gap-2">
            <form method="post" action="{{ route('admin.sources.recrawl', $source) }}">@csrf
                <button class="px-4 py-2 rounded-lg bg-brand text-white text-sm">今すぐ再取得</button>
            </form>
            <a href="{{ route('admin.sources.edit', $source) }}" class="px-4 py-2 rounded-lg border border-stone-300 text-sm">設定</a>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-stone-200 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-slate-500 border-b border-stone-100">
                <tr><th class="px-4 py-2">取得日時</th><th class="px-4 py-2">HTTP</th><th class="px-4 py-2">結果</th><th class="px-4 py-2">差分</th><th class="px-4 py-2">抽出</th><th class="px-4 py-2">エラー</th></tr>
            </thead>
            <tbody>
                @forelse ($runs as $run)
                    @php
                        $rc = ['changed'=>'bg-accent/20 text-accent-dark','unchanged'=>'bg-stone-100 text-slate-500','failed'=>'bg-red-100 text-red-600','success'=>'bg-stone-100 text-slate-500'][$run->result] ?? 'bg-stone-100';
                        $dc = ['new'=>'新規','updated'=>'更新','removed'=>'削除','none'=>'—','unchanged'=>'変更なし'][$run->diff_status] ?? $run->diff_status;
                    @endphp
                    <tr class="border-b border-stone-50">
                        <td class="px-4 py-3 text-slate-500 whitespace-nowrap">{{ optional($run->fetched_at)->format('Y/n/j H:i:s') }}</td>
                        <td class="px-4 py-3">{{ $run->http_status ?? '—' }}</td>
                        <td class="px-4 py-3"><span class="px-2 py-0.5 rounded text-xs {{ $rc }}">{{ $run->result }}</span></td>
                        <td class="px-4 py-3">{{ $dc }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $run->extraction_runs_count }}件</td>
                        <td class="px-4 py-3 text-red-500 max-w-xs truncate">{{ $run->error_message }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">取得履歴がありません。</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $runs->links() }}</div>
@endsection
