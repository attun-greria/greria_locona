@extends('layouts.public')

@section('title', '自治体からさがす｜LOCONA')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-brand-dark mb-6">自治体からさがす</h1>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($municipalities as $municipality)
            @include('public.municipalities._card', ['municipality' => $municipality])
        @empty
            <p class="text-slate-500">自治体は準備中です。</p>
        @endforelse
    </div>

    <div class="mt-6">{{ $municipalities->links() }}</div>
</div>
@endsection
