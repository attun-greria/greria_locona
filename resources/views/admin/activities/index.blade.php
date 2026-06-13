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
        <a href="{{ route('admin.activities.create') }}" class="px-4 py-2 rounded-lg bg-brand text-white text-sm whitespace-nowrap">＋ 活動を追加</a>
    </div>

    <div class="bg-white rounded-xl border border-stone-200 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-slate-500 border-b border-stone-100">
                <tr>
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
                        <td class="px-4 py-3 font-medium max-w-xs truncate">{{ $a->title }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $a->municipality->name }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $a->category?->name ?? '—' }}</td>
                        <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs {{ $badge }}">{{ $a->statusLabel() }}</span></td>
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
                    <tr><td colspan="7" class="px-4 py-10 text-center text-slate-400">活動がありません。</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $activities->links() }}</div>
@endsection
