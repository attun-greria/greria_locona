@extends('layouts.admin')

@section('title', '自治体管理')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <form method="get" class="flex gap-2 text-sm">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="自治体名で検索" class="px-3 py-2 border border-stone-300 rounded-lg">
            <button class="px-4 py-2 rounded-lg bg-brand text-white">検索</button>
        </form>
        <a href="{{ route('admin.municipalities.create') }}" class="px-4 py-2 rounded-lg bg-brand text-white text-sm">＋ 自治体を追加</a>
    </div>

    <div class="bg-white rounded-xl border border-stone-200 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-slate-500 border-b border-stone-100">
                <tr><th class="px-4 py-2">自治体名</th><th class="px-4 py-2">都道府県</th><th class="px-4 py-2">活動数</th><th class="px-4 py-2">公開</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($municipalities as $m)
                    <tr class="border-b border-stone-50 hover:bg-stone-50">
                        <td class="px-4 py-3 font-medium">{{ $m->name }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $m->prefecture }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $m->activities_count }}</td>
                        <td class="px-4 py-3">@if($m->is_published)<span class="text-accent-dark">公開</span>@else<span class="text-slate-400">非公開</span>@endif</td>
                        <td class="px-4 py-3 text-right"><a href="{{ route('admin.municipalities.edit', $m) }}" class="text-brand hover:underline">編集</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-slate-400">自治体がありません。</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $municipalities->links() }}</div>
@endsection
