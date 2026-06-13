@extends('layouts.public')

@section('content')
    {{-- ヒーロー（PUB-001） --}}
    <section class="bg-gradient-to-br from-brand to-brand-light text-white">
        <div class="max-w-6xl mx-auto px-4 py-16">
            <h1 class="text-3xl md:text-4xl font-bold leading-snug">地域への「よりみち」を、<br class="md:hidden">見つけよう。</h1>
            <p class="mt-4 text-stone-100 max-w-xl">週末の農業体験から、副業・プロボノ、二地域居住まで。地域、テーマ、時期、参加条件から、自分に合う関わり方を探せます。</p>

            {{-- 検索フォーム（PUB-003） --}}
            <form action="{{ route('activities.index') }}" method="get" class="mt-8 bg-white rounded-xl p-3 flex flex-col sm:flex-row gap-2 max-w-2xl shadow-lg">
                <input type="text" name="q" placeholder="キーワード（例：農業体験、副業）"
                       class="flex-1 px-4 py-3 rounded-lg text-slate-800 border border-stone-200 focus:ring-2 focus:ring-brand outline-none">
                <button class="px-6 py-3 rounded-lg bg-accent hover:bg-accent-dark text-brand-dark font-bold transition">さがす</button>
            </form>
        </div>
    </section>

    {{-- テーマ別入口（PUB-001 検索軸） --}}
    <section class="max-w-6xl mx-auto px-4 py-12">
        <h2 class="text-xl font-bold text-brand-dark mb-5">テーマからさがす</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
            @forelse ($categories as $category)
                <a href="{{ route('activities.index', ['category' => $category->slug]) }}"
                   class="group bg-white border border-stone-200 rounded-xl p-4 hover:border-brand hover:shadow-md transition">
                    <div class="text-2xl">{{ $category->icon ?: '📍' }}</div>
                    <div class="mt-2 font-semibold text-slate-800 group-hover:text-brand">{{ $category->name }}</div>
                    <div class="text-xs text-slate-500">{{ $category->activities_count }}件</div>
                </a>
            @empty
                <p class="text-slate-500 text-sm">カテゴリは準備中です。</p>
            @endforelse
        </div>
    </section>

    {{-- 注目の活動（PUB-001） --}}
    @if ($featured->isNotEmpty())
        <section class="max-w-6xl mx-auto px-4 py-4">
            <h2 class="text-xl font-bold text-brand-dark mb-5">注目の活動</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @each('public.activities._card', $featured, 'activity')
            </div>
        </section>
    @endif

    {{-- 最新の活動 --}}
    <section class="max-w-6xl mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-xl font-bold text-brand-dark">最新の活動</h2>
            <a href="{{ route('activities.index') }}" class="text-sm text-brand hover:underline">すべて見る →</a>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @each('public.activities._card', $latest, 'activity', 'public.activities._empty')
        </div>
    </section>

    {{-- 自治体への入口（PUB-001） --}}
    <section class="max-w-6xl mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-xl font-bold text-brand-dark">自治体からさがす</h2>
            <a href="{{ route('municipalities.index') }}" class="text-sm text-brand hover:underline">すべて見る →</a>
        </div>
        <div class="flex flex-wrap gap-2">
            @foreach ($municipalities as $m)
                <a href="{{ route('municipalities.show', $m) }}"
                   class="px-4 py-2 bg-white border border-stone-200 rounded-full text-sm hover:border-brand hover:text-brand transition">
                    {{ $m->name }}<span class="text-slate-400 ml-1">{{ $m->activities_count }}</span>
                </a>
            @endforeach
        </div>
    </section>
@endsection
