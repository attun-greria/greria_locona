@extends('layouts.admin')

@section('title', '活動管理')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <form method="get" class="flex flex-wrap gap-2 text-sm">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="活動名で検索" class="px-3 py-2 border border-stone-300 rounded-lg">
            <select name="status" class="px-3 py-2 border border-stone-300 rounded-lg bg-white">
                <option value="">すべての状態</option>
                @foreach (\App\Models\Activity::STATUSES as $s)
                    <option value="{{ $s }}" @selected(request('status')===$s)>{{ $s }}</option>
                @endforeach
            </select>
            <select name="municipality" class="px-3 py-2 border border-stone-300 rounded-lg bg-white">
                <option value="">すべての自治体</option>
                @foreach ($municipalities as $m)
                    <option value="{{ $m->id }}" @selected(request('municipality')==$m->id)>{{ $m->name }}</option>
                @endforeach
            </select>
            <button class="px-4 py-2 rounded-lg bg-brand text-white">絞り込み</button>
        </form>
        <div class="flex gap-2">
            <a href="{{ route('admin.csv.import') }}" class="px-3 py-2 rounded-lg border border-stone-300 text-sm whitespace-nowrap hover:border-brand">CSV入出力</a>
            <a href="{{ route('admin.activities.create') }}" class="px-4 py-2 rounded-lg bg-brand text-white text-sm whitespace-nowrap">＋ 活動を追加</a>
        </div>
    </div>

    {{-- 一括操作フォーム本体（form属性でチェックボックスと関連付け、表のネストを避ける） --}}
    <form method="post" action="{{ route('admin.activities.bulk') }}" id="bulkForm">@csrf</form>
    <div class="bg-white rounded-xl border border-stone-200 overflow-x-auto">
        {{-- 一括操作バー（ADM-011） --}}
        <div class="px-4 py-2 border-b border-stone-100 flex items-center gap-2 text-sm bg-stone-50">
            <span class="text-slate-500">一括操作:</span>
            <select name="bulk_action" form="bulkForm" class="px-2 py-1 border border-stone-300 rounded bg-white">
                <option value="publish">公開</option>
                <option value="unpublish">非公開(下書き)</option>
                <option value="archive">アーカイブ</option>
                <option value="verify">再確認日を今日に</option>
            </select>
            <button form="bulkForm" class="px-3 py-1 rounded bg-brand text-white" onclick="return confirm('選択した活動に一括操作を実行しますか？')">適用</button>
            <span class="text-xs text-slate-400">※ 左のチェックで選択</span>
        </div>
        <table class="w-full text-sm">
            <thead class="text-left text-slate-500 border-b border-stone-100">
                <tr>
                    <th class="px-3 py-2"><input type="checkbox" onclick="document.querySelectorAll('.rowchk').forEach(c=>c.checked=this.checked)"></th>
                    <th class="px-4 py-2">活動名</th><th class="px-4 py-2">自治体</th>
                    <th class="px-4 py-2">カテゴリ</th><th class="px-4 py-2">状態</th>
                    <th class="px-4 py-2">締切</th><th class="px-4 py-2">クリック</th><th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($activities as $a)
                    @php
                        $badge = ['draft'=>'bg-stone-100 text-slate-600','review'=>'bg-amber-100 text-amber-700','published'=>'bg-accent/20 text-accent-dark','archived'=>'bg-stone-200 text-slate-500','rejected'=>'bg-red-100 text-red-600'][$a->status];
                    @endphp
                    <tr class="border-b border-stone-50 hover:bg-stone-50">
                        <td class="px-3 py-3"><input type="checkbox" class="rowchk" name="ids[]" value="{{ $a->id }}" form="bulkForm"></td>
                        <td class="px-4 py-3 font-medium max-w-xs truncate">{{ $a->title }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $a->municipality->name }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $a->category?->name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs {{ $badge }}">{{ $a->statusLabel() }}</span>
                            @if ($a->visibility === 'internal')
                                <span class="block mt-1 text-xs px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 w-fit">社内のみ</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-500">{{ optional($a->application_deadline)->format('y/n/j') ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $a->click_count }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.activities.edit', $a) }}" class="text-brand hover:underline">編集</a>
                            <form action="{{ route('admin.activities.duplicate', $a) }}" method="post" class="inline">@csrf
                                <button class="ml-2 text-slate-400 hover:text-brand">複製</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-10 text-center text-slate-400">活動がありません。</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $activities->links() }}</div>
@endsection
