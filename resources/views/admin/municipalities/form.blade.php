@extends('layouts.admin')

@section('title', $municipality->exists ? '自治体を編集' : '自治体を追加')

@section('content')
@php $isEdit = $municipality->exists; @endphp
<form method="post" action="{{ $isEdit ? route('admin.municipalities.update', $municipality) : route('admin.municipalities.store') }}" class="max-w-2xl space-y-5">
    @csrf
    @if ($isEdit) @method('PUT') @endif

    <div class="bg-white rounded-xl border border-stone-200 p-5 space-y-4">
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold mb-1">自治体名 <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $municipality->name) }}" required class="w-full px-3 py-2 border border-stone-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">都道府県 <span class="text-red-500">*</span></label>
                <input type="text" name="prefecture" value="{{ old('prefecture', $municipality->prefecture) }}" required class="w-full px-3 py-2 border border-stone-300 rounded-lg">
            </div>
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1">市区町村</label>
            <input type="text" name="city" value="{{ old('city', $municipality->city) }}" class="w-full px-3 py-2 border border-stone-300 rounded-lg">
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1">概要</label>
            <textarea name="summary" rows="3" class="w-full px-3 py-2 border border-stone-300 rounded-lg">{{ old('summary', $municipality->summary) }}</textarea>
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold mb-1">公式サイトURL</label>
                <input type="url" name="official_url" value="{{ old('official_url', $municipality->official_url) }}" class="w-full px-3 py-2 border border-stone-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">LINE導線URL</label>
                <input type="url" name="line_url" value="{{ old('line_url', $municipality->line_url) }}" class="w-full px-3 py-2 border border-stone-300 rounded-lg">
            </div>
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold mb-1">相談窓口名</label>
                <input type="text" name="contact_name" value="{{ old('contact_name', $municipality->contact_name) }}" class="w-full px-3 py-2 border border-stone-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">相談窓口URL</label>
                <input type="url" name="contact_url" value="{{ old('contact_url', $municipality->contact_url) }}" class="w-full px-3 py-2 border border-stone-300 rounded-lg">
            </div>
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1">関連サイトURL（1行1URL）</label>
            <textarea name="related_urls_text" rows="3" class="w-full px-3 py-2 border border-stone-300 rounded-lg">{{ old('related_urls_text', implode("\n", $municipality->related_urls ?? [])) }}</textarea>
        </div>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $municipality->is_published ?? true)) class="rounded text-brand focus:ring-brand">
            公開する
        </label>
    </div>

    <div class="flex items-center gap-3">
        <button class="px-6 py-2.5 rounded-lg bg-brand text-white font-bold hover:bg-brand-dark">{{ $isEdit ? '更新する' : '登録する' }}</button>
        <a href="{{ route('admin.municipalities.index') }}" class="text-slate-500 hover:underline text-sm">キャンセル</a>
    </div>
</form>

@if ($isEdit)
    <form action="{{ route('admin.municipalities.destroy', $municipality) }}" method="post" class="mt-4 max-w-2xl" onsubmit="return confirm('削除すると紐づく活動も削除されます。よろしいですか？')">
        @csrf @method('DELETE')
        <button class="text-sm text-red-500 hover:underline">この自治体を削除</button>
    </form>
@endif
@endsection
