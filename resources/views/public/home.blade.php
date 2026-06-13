@extends('layouts.public')

@section('content')
    {{-- ヒーロー（PUB-001） --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-brand-dark via-brand to-brand-light text-white">
        <div class="pointer-events-none absolute -right-16 -top-16 w-72 h-72 rounded-full bg-accent/20 blur-2xl"></div>
        <div class="pointer-events-none absolute right-24 bottom-8 w-4 h-4 rounded-full bg-accent"></div>
        <div class="relative max-w-6xl mx-auto px-4 py-20">
            <span class="inline-flex items-center gap-1.5 text-xs font-semibold tracking-widest text-accent">
                <span class="w-1.5 h-1.5 rounded-full bg-accent"></span>LOCAL × CONNECT × NAVIGATION
            </span>
            <h1 class="mt-4 text-3xl md:text-5xl font-bold leading-tight tracking-tight">地域への「よりみち」を、<br class="md:hidden">見つけよう。</h1>
            <p class="mt-4 text-stone-100 max-w-xl leading-relaxed">週末の農業体験から、副業・プロボノ、二地域居住まで。地域、テーマ、時期、参加条件から、自分に合う関わり方を探せます。</p>

            {{-- 検索フォーム（PUB-003） --}}
            <form action="{{ route('activities.index') }}" method="get" class="mt-8 bg-white rounded-2xl p-2.5 flex flex-col sm:flex-row gap-2 max-w-2xl shadow-2xl shadow-brand-dark/30">
                <input type="text" name="q" placeholder="キーワード（例：農業体験、副業）"
                       class="flex-1 px-4 py-3 rounded-xl text-slate-800 border border-stone-200 focus:ring-2 focus:ring-brand outline-none">
                <button class="lo-btn-accent px-7 py-3">さがす</button>
            </form>

            {{-- 人気テーマのクイック導線 --}}
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach ($categories->take(6) as $c)
                    <a href="{{ route('activities.index', ['category' => $c->slug]) }}"
                       class="px-3 py-1.5 rounded-full bg-white/10 hover:bg-white/20 text-sm text-white transition">{{ $c->icon }} {{ $c->name }}</a>
                @endforeach
            </div>
        </div>

        {{-- 実績ストリップ（ポータル感） --}}
        <div class="relative bg-brand-dark/40 backdrop-blur border-t border-white/10">
            <div class="max-w-6xl mx-auto px-4 py-4 grid grid-cols-3 divide-x divide-white/10 text-center">
                <div><div class="text-2xl font-bold">{{ number_format($stats['activities']) }}</div><div class="text-xs text-stone-200">公開中の活動</div></div>
                <div><div class="text-2xl font-bold">{{ number_format($stats['municipalities']) }}</div><div class="text-xs text-stone-200">掲載自治体</div></div>
                <div><div class="text-2xl font-bold">{{ number_format($stats['categories']) }}</div><div class="text-xs text-stone-200">テーマ</div></div>
            </div>
        </div>
    </section>

    {{-- テーマ別入口（PUB-001 検索軸） --}}
    <section class="max-w-6xl mx-auto px-4 py-12">
        <h2 class="lo-section-title mb-5">テーマからさがす</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
            @forelse ($categories as $category)
                <a href="{{ route('activities.index', ['category' => $category->slug]) }}"
                   class="lo-card-link group flex items-center gap-3 p-3">
                    <div class="w-12 h-12 shrink-0">
                        <x-cover :seed="$category->slug" :icon="$category->icon ?: '📍'" rounded="rounded-xl" />
                    </div>
                    <div>
                        <div class="font-semibold text-slate-800 group-hover:text-brand text-sm">{{ $category->name }}</div>
                        <div class="text-xs text-slate-500">{{ $category->activities_count }}件</div>
                    </div>
                </a>
            @empty
                <p class="text-slate-500 text-sm">カテゴリは準備中です。</p>
            @endforelse
        </div>
    </section>

    {{-- 締切が近い活動（時間軸セクション） --}}
    @if ($deadlineSoon->isNotEmpty())
        <section class="max-w-6xl mx-auto px-4 py-4">
            <h2 class="lo-section-title mb-5">締切が近い活動</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @each('public.activities._card', $deadlineSoon, 'activity')
            </div>
        </section>
    @endif

    {{-- 注目の活動（PUB-001） --}}
    @if ($featured->isNotEmpty())
        <section class="max-w-6xl mx-auto px-4 py-8">
            <h2 class="lo-section-title mb-5">注目の活動</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @each('public.activities._card', $featured, 'activity')
            </div>
        </section>
    @endif

    {{-- 注目の自治体（紹介コンテンツ） --}}
    @if ($municipalities->isNotEmpty())
        <section class="bg-white border-y border-stone-200">
            <div class="max-w-6xl mx-auto px-4 py-12">
                <div class="flex items-center justify-between mb-1">
                    <h2 class="lo-section-title">地域・自治体を知る</h2>
                    <a href="{{ route('municipalities.index') }}" class="text-sm text-brand hover:underline">すべて見る →</a>
                </div>
                <p class="text-sm text-slate-500 mb-5">各地域の特色と、いま参加できる活動・LINE窓口を紹介します。</p>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @each('public.municipalities._card', $municipalities, 'municipality')
                </div>
            </div>
        </section>
    @endif

    {{-- 最新の活動 --}}
    <section class="max-w-6xl mx-auto px-4 py-12">
        <div class="flex items-center justify-between mb-5">
            <h2 class="lo-section-title">最新の活動</h2>
            <a href="{{ route('activities.index') }}" class="text-sm text-brand hover:underline">すべて見る →</a>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @each('public.activities._card', $latest, 'activity', 'public.activities._empty')
        </div>
    </section>

    {{-- 地域からさがす --}}
    @if ($prefectures->isNotEmpty())
        <section class="max-w-6xl mx-auto px-4 pb-16">
            <h2 class="lo-section-title mb-5">地域からさがす</h2>
            <div class="flex flex-wrap gap-2">
                @foreach ($prefectures as $pref)
                    <a href="{{ route('activities.index', ['prefecture' => $pref]) }}"
                       class="px-4 py-2 bg-white border border-stone-200 rounded-full text-sm hover:border-brand hover:text-brand transition">{{ $pref }}</a>
                @endforeach
            </div>
        </section>
    @endif
@endsection
