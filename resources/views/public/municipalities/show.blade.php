@extends('layouts.public')

@section('title', $activity_title ?? $municipality->name.'｜LOCONA')
@section('meta_description', \Illuminate\Support\Str::limit($municipality->summary, 110))
@section('canonical', route('municipalities.show', $municipality))

@section('content')
<div class="relative overflow-hidden bg-gradient-to-br from-brand-dark via-brand to-brand-light text-white">
    @if ($municipality->image_url)
        <img src="{{ $municipality->image_url }}" alt="" class="absolute inset-0 w-full h-full object-cover opacity-30">
    @endif
    <div class="pointer-events-none absolute -right-16 -top-16 w-72 h-72 rounded-full bg-accent/20 blur-2xl"></div>
    <div class="relative max-w-5xl mx-auto px-4 py-14">
        <div class="text-sm text-accent font-medium">{{ $municipality->prefecture }}</div>
        <h1 class="mt-1 text-3xl md:text-4xl font-bold">{{ $municipality->name }}</h1>
        @if ($municipality->catchphrase)
            <p class="mt-2 text-lg text-white/90 font-medium">{{ $municipality->catchphrase }}</p>
        @endif
        @if ($municipality->summary)
            <p class="mt-4 max-w-2xl text-stone-100 leading-relaxed">{{ $municipality->summary }}</p>
        @endif
        <div class="mt-6 flex flex-wrap gap-3">
            @if ($municipality->official_url)
                <a href="{{ $municipality->official_url }}" rel="nofollow" class="px-4 py-2 rounded-lg bg-white/15 hover:bg-white/25 text-sm">公式サイト</a>
            @endif
            @if ($municipality->contact_url)
                <a href="{{ $municipality->contact_url }}" rel="nofollow" class="px-4 py-2 rounded-lg bg-white/15 hover:bg-white/25 text-sm">{{ $municipality->contact_name ?: '相談窓口' }}</a>
            @endif
            @if ($municipality->line_url)
                <a href="{{ $municipality->line_url }}" rel="nofollow" class="px-4 py-2 rounded-lg bg-[#06C755] hover:opacity-90 text-sm font-bold">LINEでつながる</a>
            @endif
        </div>
    </div>
</div>

<div class="max-w-5xl mx-auto px-4 py-10">
    @if (!empty($municipality->related_urls))
        <section class="mb-8">
            <h2 class="text-sm font-bold text-slate-500 mb-2">関連リンク</h2>
            <div class="flex flex-wrap gap-2">
                @foreach ($municipality->related_urls as $url)
                    <a href="{{ $url }}" rel="nofollow" class="px-3 py-1.5 bg-white border border-stone-200 rounded-full text-sm hover:border-brand hover:text-brand">{{ parse_url($url, PHP_URL_HOST) }}</a>
                @endforeach
            </div>
        </section>
    @endif

    <h2 class="text-xl font-bold text-brand-dark mb-4">公開中の活動</h2>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @each('public.activities._card', $activities, 'activity', 'public.activities._empty')
    </div>
    <div class="mt-6">{{ $activities->links() }}</div>
</div>
@endsection
