@extends('layouts.public')

@section('title', ($activity->meta_title ?: $activity->title).'｜LOCONA')
@section('meta_description', $activity->meta_description ?: \Illuminate\Support\Str::limit($activity->summary, 110))
@section('canonical', route('activities.show', $activity))

@push('head')
    {{-- 構造化データ PUB-011 --}}
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Event',
        'name' => $activity->title,
        'description' => $activity->summary,
        'startDate' => optional($activity->start_at)->toIso8601String(),
        'endDate' => optional($activity->end_at)->toIso8601String(),
        'eventAttendanceMode' => $activity->online_available ? 'https://schema.org/OnlineEventAttendanceMode' : 'https://schema.org/OfflineEventAttendanceMode',
        'location' => ['@type' => 'Place', 'name' => $activity->municipality->name, 'address' => $activity->municipality->prefecture],
        'organizer' => ['@type' => 'Organization', 'name' => $activity->organizer_name ?: $activity->municipality->name],
        'url' => route('activities.show', $activity),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endpush

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    {{-- パンくず --}}
    <nav class="text-sm text-slate-500 mb-4">
        <a href="{{ route('home') }}" class="hover:text-brand">ホーム</a> ›
        <a href="{{ route('activities.index') }}" class="hover:text-brand">活動</a> ›
        <span class="text-slate-700">{{ \Illuminate\Support\Str::limit($activity->title, 24) }}</span>
    </nav>

    {{-- ヒーローカバー --}}
    <div class="h-48 md:h-60 mb-6">
        <x-cover :seed="$activity->slug" :icon="$activity->category?->icon ?: '📍'"
                 :image="$activity->image_url" rounded="rounded-2xl" />
    </div>

    @if ($activity->isExpired())
        <div class="mb-4 px-4 py-3 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-sm">この活動は募集・開催を終了している可能性があります。最新情報は一次情報でご確認ください。</div>
    @endif

    <div class="flex flex-wrap items-center gap-2 mb-3">
        @if ($activity->kind === 'program')
            <span class="px-3 py-1 rounded-full bg-indigo-100 text-indigo-700 text-sm font-medium">制度・支援</span>
        @elseif ($activity->kind === 'intro')
            <span class="px-3 py-1 rounded-full bg-rose-100 text-rose-700 text-sm font-medium">相談・紹介</span>
        @endif
        @if ($activity->category)
            <span class="px-3 py-1 rounded-full bg-brand/10 text-brand text-sm font-medium">{{ $activity->category->name }}</span>
        @endif
        <a href="{{ route('municipalities.show', $activity->municipality) }}" class="px-3 py-1 rounded-full bg-stone-100 text-slate-600 text-sm hover:text-brand">📍 {{ $activity->municipality->name }}</a>
    </div>

    <h1 class="text-2xl md:text-3xl font-bold text-brand-dark leading-snug">{{ $activity->title }}</h1>
    <p class="mt-4 text-slate-700 leading-relaxed">{{ $activity->summary }}</p>

    @if ($activity->description)
        <div class="mt-4 text-slate-700 leading-relaxed whitespace-pre-line">{{ $activity->description }}</div>
    @endif

    {{-- 引用ブロック（明瞭区別・出所明示／著作権法32条） --}}
    @if ($activity->quote_text)
        <figure class="mt-5">
            <blockquote class="border-l-4 border-brand bg-stone-50 rounded-r-lg px-4 py-3 text-slate-700 leading-relaxed">{{ $activity->quote_text }}</blockquote>
            @if ($activity->quote_source)
                <figcaption class="mt-1 text-xs text-slate-500">出典：{{ $activity->quote_source }}</figcaption>
            @endif
        </figure>
    @endif

    {{-- 共通項目テーブル（PUB-005） --}}
    <dl class="mt-8 grid sm:grid-cols-2 gap-x-8 gap-y-3 bg-white border border-stone-200 rounded-xl p-6 text-sm">
        @php
            $rows = [
                ['主催者', $activity->organizer_name],
                ['開催日', $activity->start_at ? $activity->start_at->format('Y年n月j日') . ($activity->end_at && !$activity->end_at->isSameDay($activity->start_at) ? '〜'.$activity->end_at->format('n月j日') : '') : ($activity->is_recurring ? '随時・定期開催' : null)],
                ['申込締切', optional($activity->application_deadline)->format('Y年n月j日')],
                ['費用・報酬', $activity->fee_text],
                ['対象者', $activity->target_audience],
                ['定員', $activity->capacity ? $activity->capacity.'名' : null],
            ];
        @endphp
        @foreach ($rows as [$label, $value])
            @if ($value)
                <div class="flex flex-col">
                    <dt class="text-slate-500">{{ $label }}</dt>
                    <dd class="font-medium text-slate-800">{{ $value }}</dd>
                </div>
            @endif
        @endforeach
    </dl>

    {{-- 参加条件バッジ --}}
    <div class="mt-4 flex flex-wrap gap-2">
        @foreach ([
            'child_friendly' => '👨‍👩‍👧 子連れ可',
            'beginner_friendly' => '🔰 初心者可',
            'online_available' => '💻 オンライン可',
            'has_reward' => '💰 報酬あり',
            'transport_support' => '🚃 交通費支援',
            'lodging_support' => '🏠 宿泊支援',
        ] as $flag => $label)
            @if ($activity->$flag)
                <span class="px-3 py-1 rounded-full bg-accent/15 text-accent-dark text-sm">{{ $label }}</span>
            @endif
        @endforeach
    </div>

    {{-- 送客CTA（PUB-006）。種別に応じて文言を変える。 --}}
    @php
        $ctaLabel = match ($activity->kind) {
            'program' => '制度の詳細・申請方法を確認する →',
            'intro' => '相談・問い合わせ窓口を確認する →',
            default => '申込・詳細を一次情報で確認する →',
        };
    @endphp
    <div class="mt-8 flex flex-col sm:flex-row gap-3">
        <a href="{{ route('outbound.redirect', ['activity' => $activity, 'type' => 'apply']) }}" rel="nofollow"
           class="flex-1 text-center px-6 py-4 rounded-xl bg-accent hover:bg-accent-dark text-brand-dark font-bold transition">
            {{ $ctaLabel }}
        </a>
        @if ($activity->municipality->line_url)
            <a href="{{ $activity->municipality->line_url }}" rel="nofollow"
               class="px-6 py-4 rounded-xl border-2 border-[#06C755] text-[#06C755] font-bold text-center hover:bg-[#06C755] hover:text-white transition">
                LINEで地域とつながる
            </a>
        @endif
    </div>
    {{-- 出所明示（48条）。公開情報を整理した編集情報である旨と出典を明記。 --}}
    <div class="mt-3 text-xs text-slate-500 bg-stone-50 border border-stone-200 rounded-lg px-3 py-2">
        本ページは公開情報をもとにLOCONA編集部が整理した情報です。詳細・最新情報は一次情報をご確認ください。<br>
        出典：<a href="{{ route('outbound.redirect', $activity) }}" rel="nofollow" class="underline hover:text-brand">{{ $activity->attribution_name ?: parse_url($activity->source_url, PHP_URL_HOST) }}</a>
        @if ($activity->cited_at)　（取得日：{{ $activity->cited_at->format('Y年n月j日') }}）@endif
        　/　最終確認日：{{ optional($activity->verified_at)->format('Y年n月j日') ?? '未確認' }}
    </div>

    {{-- 修正・削除依頼（ADM-014 / SEC-012） --}}
    <section class="mt-12 border-t border-stone-200 pt-6">
        @if (session('correction_sent'))
            <div class="mb-3 px-4 py-3 rounded-lg bg-accent/15 border border-accent text-accent-dark text-sm">{{ session('correction_sent') }}</div>
        @endif
        <details class="text-sm" @if($errors->any()) open @endif>
            <summary class="cursor-pointer text-slate-500 hover:text-brand">この情報の誤り・修正・掲載削除を依頼する</summary>
            <form method="post" action="{{ route('activities.correction', $activity) }}" class="mt-4 bg-white border border-stone-200 rounded-xl p-5 space-y-3 max-w-xl">
                @csrf
                @error('message')<p class="text-red-600">{{ $message }}</p>@enderror
                <div>
                    <label class="block font-semibold mb-1">依頼の種類</label>
                    <select name="type" class="w-full px-3 py-2 border border-stone-300 rounded-lg">
                        <option value="correction">情報の修正</option>
                        <option value="deletion">掲載の削除</option>
                        <option value="other">その他</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold mb-1">内容 <span class="text-red-500">*</span></label>
                    <textarea name="message" rows="3" required class="w-full px-3 py-2 border border-stone-300 rounded-lg" placeholder="誤りの箇所や修正内容をご記入ください">{{ old('message') }}</textarea>
                </div>
                <div class="grid sm:grid-cols-2 gap-3">
                    <input type="text" name="requester_name" value="{{ old('requester_name') }}" placeholder="お名前・団体名（任意）" class="px-3 py-2 border border-stone-300 rounded-lg">
                    <input type="email" name="requester_email" value="{{ old('requester_email') }}" placeholder="返信先メール（任意）" class="px-3 py-2 border border-stone-300 rounded-lg">
                </div>
                <button class="px-5 py-2 rounded-lg bg-brand text-white font-bold">送信する</button>
            </form>
        </details>
    </section>

    {{-- 関連活動（PUB-008） --}}
    @if ($related->isNotEmpty())
        <section class="mt-12">
            <h2 class="text-lg font-bold text-brand-dark mb-4">関連する活動</h2>
            <div class="grid sm:grid-cols-2 gap-4">
                @each('public.activities._card', $related, 'activity')
            </div>
        </section>
    @endif
</div>
@endsection
