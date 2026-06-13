{{-- 自治体紹介カード（ポータルの自治体コンテンツ） --}}
<a href="{{ route('municipalities.show', $municipality) }}" class="lo-card-link flex flex-col overflow-hidden">
    <div class="h-28 shrink-0">
        <x-cover :seed="$municipality->slug" icon="🏞" :image="$municipality->image_url" :label="$municipality->prefecture" />
    </div>
    <div class="p-4 flex-1 flex flex-col">
        <h3 class="font-bold text-slate-800 group-hover:text-brand">{{ $municipality->name }}</h3>
        @if ($municipality->catchphrase)
            <p class="mt-0.5 text-sm text-brand font-medium">{{ $municipality->catchphrase }}</p>
        @endif
        @if ($municipality->summary)
            <p class="mt-1.5 text-sm text-slate-500 line-clamp-2 flex-1">{{ $municipality->summary }}</p>
        @endif
        <div class="mt-3 flex items-center justify-between text-xs">
            <span class="text-brand font-medium">公開中の活動 {{ $municipality->activities_count }}件</span>
            @if ($municipality->line_url)
                <span class="px-2 py-0.5 rounded-full bg-[#06C755]/10 text-[#06C755] font-medium">LINE</span>
            @endif
        </div>
    </div>
</a>
