@extends('layouts.admin')

@section('title', 'カテゴリ管理')

@section('content')
<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white rounded-xl border border-stone-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="text-left text-slate-500 border-b border-stone-100">
                <tr><th class="px-4 py-2">表示順</th><th class="px-4 py-2">アイコン</th><th class="px-4 py-2">名称</th><th class="px-4 py-2">活動数</th><th class="px-4 py-2">状態</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($categories as $c)
                    <tr class="border-b border-stone-50">
                        <td class="px-4 py-2">
                            <form method="post" action="{{ route('admin.categories.update', $c) }}" class="flex items-center gap-2">@csrf @method('PUT')
                                <input type="hidden" name="name" value="{{ $c->name }}">
                                <input type="number" name="display_order" value="{{ $c->display_order }}" class="w-16 px-2 py-1 border border-stone-300 rounded">
                                <input type="text" name="icon" value="{{ $c->icon }}" class="w-12 px-2 py-1 border border-stone-300 rounded text-center">
                                <label class="text-xs flex items-center gap-1"><input type="checkbox" name="is_active" value="1" @checked($c->is_active)>有効</label>
                                <button class="text-xs text-brand hover:underline">保存</button>
                            </form>
                        </td>
                        <td class="px-4 py-2 text-lg">{{ $c->icon }}</td>
                        <td class="px-4 py-2 font-medium">{{ $c->name }}</td>
                        <td class="px-4 py-2 text-slate-500">{{ $c->activities_count }}</td>
                        <td class="px-4 py-2">@if($c->is_active)<span class="text-accent-dark text-xs">有効</span>@else<span class="text-slate-400 text-xs">無効</span>@endif</td>
                        <td class="px-4 py-2 text-right">
                            <form method="post" action="{{ route('admin.categories.destroy', $c) }}" onsubmit="return confirm('削除しますか？')">@csrf @method('DELETE')
                                <button class="text-xs text-red-500 hover:underline">削除</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">カテゴリがありません。</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="bg-white rounded-xl border border-stone-200 p-5 h-fit">
        <h2 class="font-bold text-slate-700 text-sm mb-3">カテゴリを追加</h2>
        <form method="post" action="{{ route('admin.categories.store') }}" class="space-y-3 text-sm">@csrf
            <input type="text" name="name" placeholder="名称（例：農業・食）" required class="w-full px-3 py-2 border border-stone-300 rounded-lg">
            <input type="text" name="icon" placeholder="アイコン絵文字（例：🌾）" class="w-full px-3 py-2 border border-stone-300 rounded-lg">
            <input type="number" name="display_order" placeholder="表示順" class="w-full px-3 py-2 border border-stone-300 rounded-lg">
            <button class="w-full py-2 rounded-lg bg-brand text-white font-bold">追加</button>
        </form>
    </div>
</div>
@endsection
