@extends('layouts.public')

@section('title', '自治体からさがす｜LOCONA')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-brand-dark mb-6">自治体からさがす</h1>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($municipalities as $m)
            <a href="{{ route('municipalities.show', $m) }}"
               class="bg-white border border-stone-200 rounded-xl p-5 hover:border-brand hover:shadow-md transition">
                <div class="text-xs text-slate-500">{{ $m->prefecture }}</div>
                <h2 class="mt-1 font-bold text-slate-800">{{ $m->name }}</h2>
                <p class="mt-2 text-sm text-slate-500 line-clamp-2">{{ $m->summary }}</p>
                <div class="mt-3 text-xs text-brand font-medium">公開中の活動 {{ $m->activities_count }}件</div>
            </a>
        @empty
            <p class="text-slate-500">自治体は準備中です。</p>
        @endforelse
    </div>

    <div class="mt-6">{{ $municipalities->links() }}</div>
</div>
@endsection
