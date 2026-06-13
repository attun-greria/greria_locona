@extends('layouts.admin')

@section('title', 'タグ管理')

@section('content')
<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        @php $labels = ['search'=>'検索用タグ','line'=>'LINE運用タグ','ops'=>'運用分類タグ']; @endphp
        @foreach ($labels as $type => $label)
            <div class="bg-white rounded-xl border border-stone-200 p-5">
                <h2 class="font-bold text-slate-700 text-sm mb-3">{{ $label }}</h2>
                <div class="flex flex-wrap gap-2">
                    @forelse ($tags[$type] ?? [] as $tag)
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-stone-100 text-sm">
                            {{ $tag->name }}<span class="text-xs text-slate-400">{{ $tag->activities_count }}</span>
                            <form method="post" action="{{ route('admin.tags.destroy', $tag) }}" class="inline" onsubmit="return confirm('削除しますか？')">@csrf @method('DELETE')
                                <button class="text-slate-400 hover:text-red-500 ml-1">×</button>
                            </form>
                        </span>
                    @empty
                        <span class="text-sm text-slate-400">未登録</span>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>

    <div class="bg-white rounded-xl border border-stone-200 p-5 h-fit">
        <h2 class="font-bold text-slate-700 text-sm mb-3">タグを追加</h2>
        <form method="post" action="{{ route('admin.tags.store') }}" class="space-y-3 text-sm">@csrf
            <input type="text" name="name" placeholder="タグ名（例：農業）" required class="w-full px-3 py-2 border border-stone-300 rounded-lg">
            <select name="type" class="w-full px-3 py-2 border border-stone-300 rounded-lg bg-white">
                <option value="search">検索用タグ</option>
                <option value="line">LINE運用タグ</option>
                <option value="ops">運用分類タグ</option>
            </select>
            <button class="w-full py-2 rounded-lg bg-brand text-white font-bold">追加</button>
        </form>
    </div>
</div>
@endsection
