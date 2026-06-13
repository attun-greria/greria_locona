@extends('layouts.admin')

@section('title', '修正・削除依頼')

@section('content')
    <div class="mb-4 flex items-center gap-2 text-sm">
        <span class="text-slate-500">未対応・対応中：<span class="font-bold text-brand">{{ $openCount }}</span>件</span>
        <div class="ml-auto flex gap-2">
            @foreach (['' => 'すべて', 'open' => '未対応', 'in_progress' => '対応中', 'resolved' => '対応済み', 'rejected' => '却下'] as $k => $v)
                <a href="{{ route('admin.corrections.index', array_filter(['status' => $k])) }}"
                   class="px-3 py-1.5 rounded-lg {{ request('status', '') === $k ? 'bg-brand text-white' : 'bg-white border border-stone-300' }}">{{ $v }}</a>
            @endforeach
        </div>
    </div>

    <div class="space-y-3">
        @forelse ($requests as $r)
            <div class="bg-white rounded-xl border border-stone-200 p-5">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 text-xs mb-1">
                            <span class="px-2 py-0.5 rounded-full {{ $r->type === 'deletion' ? 'bg-red-100 text-red-600' : 'bg-stone-100 text-slate-600' }}">{{ $r->typeLabel() }}</span>
                            <span class="text-slate-400">{{ $r->created_at->format('Y/n/j H:i') }}</span>
                        </div>
                        @if ($r->activity)
                            <a href="{{ route('admin.activities.edit', $r->activity) }}" class="font-medium text-brand hover:underline">{{ $r->activity->title }}</a>
                        @else
                            <span class="font-medium text-slate-400">（対象活動は削除済み）</span>
                        @endif
                        <p class="mt-2 text-sm text-slate-700 whitespace-pre-line">{{ $r->message }}</p>
                        @if ($r->requester_name || $r->requester_email)
                            <p class="mt-2 text-xs text-slate-400">依頼者：{{ $r->requester_name }} {{ $r->requester_email }}</p>
                        @endif
                    </div>
                    <span class="shrink-0 text-xs px-2 py-1 rounded-full {{ ['open'=>'bg-amber-100 text-amber-700','in_progress'=>'bg-blue-100 text-blue-700','resolved'=>'bg-accent/20 text-accent-dark','rejected'=>'bg-stone-200 text-slate-500'][$r->status] }}">{{ $r->statusLabel() }}</span>
                </div>

                <form method="post" action="{{ route('admin.corrections.update', $r) }}" class="mt-4 flex flex-col sm:flex-row gap-2 sm:items-end">
                    @csrf @method('PATCH')
                    <div class="flex-1">
                        <label class="block text-xs text-slate-500 mb-1">対応メモ</label>
                        <input type="text" name="admin_note" value="{{ $r->admin_note }}" class="w-full px-3 py-2 border border-stone-300 rounded-lg text-sm">
                    </div>
                    <select name="status" class="px-3 py-2 border border-stone-300 rounded-lg text-sm bg-white">
                        @foreach (['open' => '未対応', 'in_progress' => '対応中', 'resolved' => '対応済み', 'rejected' => '却下'] as $k => $v)
                            <option value="{{ $k }}" @selected($r->status === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                    <button class="px-4 py-2 rounded-lg bg-brand text-white text-sm">更新</button>
                </form>
            </div>
        @empty
            <div class="bg-white rounded-xl border border-dashed border-stone-300 p-10 text-center text-slate-400">依頼はありません。</div>
        @endforelse
    </div>

    <div class="mt-4">{{ $requests->links() }}</div>
@endsection
