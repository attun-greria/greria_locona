@props([
    'seed' => '',
    'icon' => '📍',
    'label' => null,
    'image' => null,
    'rounded' => '',
])
@php
    // 著作権配慮：外部画像は権利確認済み(image_url)のときのみ。未設定はブランド連動の生成カバー。
    $palettes = [
        ['#2C6E6A', '#3E8B86'], ['#234E4B', '#3E8B86'], ['#347d77', '#9CCC4F'],
        ['#2C6E6A', '#5BA89B'], ['#1f5d59', '#6fa72c'], ['#2b6f6a', '#86b04a'],
        ['#3E8B86', '#9CCC4F'],
    ];
    $idx = abs(crc32((string) $seed)) % count($palettes);
    [$c1, $c2] = $palettes[$idx];
@endphp
@if ($image)
    <img src="{{ $image }}" alt="{{ $label }}" loading="lazy"
         {{ $attributes->merge(['class' => "w-full h-full object-cover $rounded"]) }}>
@else
    <div {{ $attributes->merge(['class' => "relative w-full h-full overflow-hidden $rounded"]) }}
         style="background:linear-gradient(135deg,{{ $c1 }} 0%,{{ $c2 }} 100%)" aria-hidden="true">
        <div class="absolute -right-5 -top-5 w-24 h-24 rounded-full bg-white/10"></div>
        <div class="absolute left-4 top-5 w-12 h-12 rounded-full bg-white/5"></div>
        <div class="absolute right-3 bottom-3 w-2.5 h-2.5 rounded-full" style="background:#9CCC4F"></div>
        <div class="absolute inset-0 flex items-center justify-center">
            <span class="text-4xl drop-shadow">{{ $icon }}</span>
        </div>
        @if ($label)
            <div class="absolute left-3 bottom-2.5 text-white text-xs font-semibold drop-shadow">{{ $label }}</div>
        @endif
    </div>
@endif
