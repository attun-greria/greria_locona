@extends('layouts.admin')

@section('title', $activity->exists ? '活動を編集' : '活動を追加')

@section('content')
@php $isEdit = $activity->exists; @endphp

<form method="post" action="{{ $isEdit ? route('admin.activities.update', $activity) : route('admin.activities.store') }}" class="grid lg:grid-cols-3 gap-6">
    @csrf
    @if ($isEdit) @method('PUT') @endif

    <div class="lg:col-span-2 space-y-5">
        <div class="bg-white rounded-xl border border-stone-200 p-5 space-y-4">
            <div>
                <label class="block text-sm font-semibold mb-1">活動名 <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title', $activity->title) }}" required class="w-full px-3 py-2 border border-stone-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">要約 <span class="text-red-500">*</span></label>
                <textarea name="summary" rows="2" required class="w-full px-3 py-2 border border-stone-300 rounded-lg">{{ old('summary', $activity->summary) }}</textarea>
                <p class="text-xs text-slate-400 mt-1">掲載用の短い要約。本文の長文転載は避け、事実情報を中心に。</p>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">説明（任意）</label>
                <textarea name="description" rows="4" class="w-full px-3 py-2 border border-stone-300 rounded-lg">{{ old('description', $activity->description) }}</textarea>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">一次情報URL <span class="text-red-500">*</span></label>
                    <input type="url" name="source_url" value="{{ old('source_url', $activity->source_url) }}" required class="w-full px-3 py-2 border border-stone-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">申込URL（任意）</label>
                    <input type="url" name="apply_url" value="{{ old('apply_url', $activity->apply_url) }}" class="w-full px-3 py-2 border border-stone-300 rounded-lg">
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">主催者（任意）</label>
                <input type="text" name="organizer_name" value="{{ old('organizer_name', $activity->organizer_name) }}" class="w-full px-3 py-2 border border-stone-300 rounded-lg">
            </div>
        </div>

        {{-- 日程 --}}
        <div class="bg-white rounded-xl border border-stone-200 p-5 space-y-4">
            <h2 class="font-bold text-slate-700 text-sm">日程・条件</h2>
            <div class="grid sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">申込締切</label>
                    <input type="date" name="application_deadline" value="{{ old('application_deadline', optional($activity->application_deadline)->format('Y-m-d')) }}" class="w-full px-3 py-2 border border-stone-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">開催開始</label>
                    <input type="datetime-local" name="start_at" value="{{ old('start_at', optional($activity->start_at)->format('Y-m-d\TH:i')) }}" class="w-full px-3 py-2 border border-stone-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">開催終了</label>
                    <input type="datetime-local" name="end_at" value="{{ old('end_at', optional($activity->end_at)->format('Y-m-d\TH:i')) }}" class="w-full px-3 py-2 border border-stone-300 rounded-lg">
                </div>
            </div>
            <div class="grid sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">費用・報酬</label>
                    <input type="text" name="fee_text" value="{{ old('fee_text', $activity->fee_text) }}" class="w-full px-3 py-2 border border-stone-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">対象者</label>
                    <input type="text" name="target_audience" value="{{ old('target_audience', $activity->target_audience) }}" class="w-full px-3 py-2 border border-stone-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">定員</label>
                    <input type="number" name="capacity" value="{{ old('capacity', $activity->capacity) }}" class="w-full px-3 py-2 border border-stone-300 rounded-lg">
                </div>
            </div>
            <div class="flex flex-wrap gap-x-5 gap-y-2 text-sm">
                @foreach ([
                    'is_recurring'=>'定期開催','child_friendly'=>'子連れ可','beginner_friendly'=>'初心者可',
                    'online_available'=>'オンライン可','has_reward'=>'報酬あり','transport_support'=>'交通費支援','lodging_support'=>'宿泊支援',
                ] as $flag=>$label)
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="{{ $flag }}" value="1" @checked(old($flag, $activity->$flag)) class="rounded text-brand focus:ring-brand">
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        </div>

        {{-- SEO --}}
        <div class="bg-white rounded-xl border border-stone-200 p-5 space-y-4">
            <h2 class="font-bold text-slate-700 text-sm">SEO（任意 / PUB-010）</h2>
            <input type="text" name="meta_title" value="{{ old('meta_title', $activity->meta_title) }}" placeholder="meta title" class="w-full px-3 py-2 border border-stone-300 rounded-lg">
            <textarea name="meta_description" rows="2" placeholder="meta description" class="w-full px-3 py-2 border border-stone-300 rounded-lg">{{ old('meta_description', $activity->meta_description) }}</textarea>
        </div>
    </div>

    {{-- サイド：公開設定 --}}
    <div class="space-y-5">
        <div class="bg-white rounded-xl border border-stone-200 p-5 space-y-4">
            <div>
                <label class="block text-sm font-semibold mb-1">自治体 <span class="text-red-500">*</span></label>
                <select name="municipality_id" required class="w-full px-3 py-2 border border-stone-300 rounded-lg bg-white">
                    <option value="">選択</option>
                    @foreach ($municipalities as $m)
                        <option value="{{ $m->id }}" @selected(old('municipality_id', $activity->municipality_id)==$m->id)>{{ $m->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">カテゴリ</label>
                <select name="category_id" class="w-full px-3 py-2 border border-stone-300 rounded-lg bg-white">
                    <option value="">未設定</option>
                    @foreach ($categories as $c)
                        <option value="{{ $c->id }}" @selected(old('category_id', $activity->category_id)==$c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">公開状態</label>
                <select name="status" class="w-full px-3 py-2 border border-stone-300 rounded-lg bg-white">
                    @foreach (\App\Models\Activity::STATUSES as $s)
                        <option value="{{ $s }}" @selected(old('status', $activity->status)===$s)>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">最終確認日</label>
                <input type="date" name="verified_at" value="{{ old('verified_at', optional($activity->verified_at)->format('Y-m-d')) }}" class="w-full px-3 py-2 border border-stone-300 rounded-lg">
            </div>
            @if ($isEdit)
                <div>
                    <label class="block text-sm font-semibold mb-1">変更理由（改訂履歴）</label>
                    <input type="text" name="revision_reason" class="w-full px-3 py-2 border border-stone-300 rounded-lg">
                </div>
            @endif
            <button class="w-full py-2.5 rounded-lg bg-brand text-white font-bold hover:bg-brand-dark">{{ $isEdit ? '更新する' : '登録する' }}</button>
        </div>

        {{-- タグ --}}
        <div class="bg-white rounded-xl border border-stone-200 p-5">
            <h2 class="font-bold text-slate-700 text-sm mb-3">タグ</h2>
            @php $selectedTags = old('tags', $activity->tags->pluck('id')->all()); @endphp
            <div class="space-y-1.5 max-h-60 overflow-y-auto text-sm">
                @foreach ($tags as $tag)
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="tags[]" value="{{ $tag->id }}" @checked(in_array($tag->id, $selectedTags)) class="rounded text-brand focus:ring-brand">
                        {{ $tag->name }} <span class="text-xs text-slate-400">{{ $tag->type }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        @if ($isEdit)
            <div class="bg-white rounded-xl border border-stone-200 p-5 text-sm">
                <a href="{{ route('activities.show', $activity) }}" target="_blank" class="text-brand hover:underline">公開ページをプレビュー ↗</a>
            </div>
        @endif
    </div>
</form>

@if ($isEdit)
    <form action="{{ route('admin.activities.destroy', $activity) }}" method="post" class="mt-4" onsubmit="return confirm('この活動を削除しますか？')">
        @csrf @method('DELETE')
        <button class="text-sm text-red-500 hover:underline">この活動を削除</button>
    </form>
@endif
@endsection
