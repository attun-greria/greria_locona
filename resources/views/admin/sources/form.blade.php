@extends('layouts.admin')

@section('title', $source->exists ? '収集元を編集' : '収集元を追加')

@section('content')
@php $isEdit = $source->exists; @endphp
<form method="post" action="{{ $isEdit ? route('admin.sources.update', $source) : route('admin.sources.store') }}" class="max-w-2xl space-y-5">
    @csrf
    @if ($isEdit) @method('PUT') @endif

    <div class="bg-white rounded-xl border border-stone-200 p-5 space-y-4">
        <div>
            <label class="block text-sm font-semibold mb-1">URL <span class="text-red-500">*</span></label>
            <input type="url" name="url" value="{{ old('url', $source->url) }}" required class="w-full px-3 py-2 border border-stone-300 rounded-lg">
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold mb-1">自治体</label>
                <select name="municipality_id" class="w-full px-3 py-2 border border-stone-300 rounded-lg bg-white">
                    <option value="">未設定</option>
                    @foreach ($municipalities as $m)
                        <option value="{{ $m->id }}" @selected(old('municipality_id', $source->municipality_id)==$m->id)>{{ $m->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">ページ種別</label>
                <select name="page_type" class="w-full px-3 py-2 border border-stone-300 rounded-lg bg-white">
                    @foreach (['official'=>'公式','tourism'=>'観光','migration'=>'移住','pdf'=>'PDF','sns'=>'SNS','other'=>'その他'] as $k=>$v)
                        <option value="{{ $k }}" @selected(old('page_type', $source->page_type)===$k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold mb-1">取得頻度</label>
                <select name="crawl_frequency" class="w-full px-3 py-2 border border-stone-300 rounded-lg bg-white">
                    @foreach (['daily'=>'日次','weekly'=>'週次','monthly'=>'月次','manual'=>'手動'] as $k=>$v)
                        <option value="{{ $k }}" @selected(old('crawl_frequency', $source->crawl_frequency)===$k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">優先度（1=高〜5=低）</label>
                <input type="number" name="priority" min="1" max="5" value="{{ old('priority', $source->priority ?? 3) }}" class="w-full px-3 py-2 border border-stone-300 rounded-lg">
            </div>
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1">利用規約確認メモ（CRW-005）</label>
            <textarea name="terms_note" rows="3" class="w-full px-3 py-2 border border-stone-300 rounded-lg">{{ old('terms_note', $source->terms_note) }}</textarea>
        </div>
        <div class="flex flex-wrap gap-5 text-sm">
            <label class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $source->is_active ?? true)) class="rounded text-brand">有効</label>
            <label class="flex items-center gap-2"><input type="checkbox" name="terms_checked" value="1" @checked(old('terms_checked', $source->terms_checked)) class="rounded text-brand">利用規約確認済み</label>
            <label class="flex items-center gap-2"><input type="checkbox" name="robots_checked" value="1" @checked(old('robots_checked', $source->robots_checked)) class="rounded text-brand">robots.txt確認済み</label>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button class="px-6 py-2.5 rounded-lg bg-brand text-white font-bold hover:bg-brand-dark">{{ $isEdit ? '更新する' : '登録する' }}</button>
        <a href="{{ route('admin.sources.index') }}" class="text-slate-500 hover:underline text-sm">キャンセル</a>
    </div>
</form>

@if ($isEdit)
    <form action="{{ route('admin.sources.destroy', $source) }}" method="post" class="mt-4" onsubmit="return confirm('削除しますか？')">@csrf @method('DELETE')
        <button class="text-sm text-red-500 hover:underline">この収集元を削除</button>
    </form>
@endif
@endsection
