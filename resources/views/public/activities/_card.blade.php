{{-- 活動カード（一覧・トップ共通） --}}
<a href="{{ route('activities.show', $activity) }}"
   class="lo-card-link flex flex-col overflow-hidden">
    <div class="p-4 flex-1">
        <div class="flex items-center gap-2 text-xs">
            @if ($activity->category)
                <span class="px-2 py-0.5 rounded-full bg-brand/10 text-brand font-medium">{{ $activity->category->name }}</span>
            @endif
            @if ($activity->online_available)
                <span class="px-2 py-0.5 rounded-full bg-accent/20 text-accent-dark font-medium">オンライン可</span>
            @endif
        </div>
        <h3 class="mt-2 font-bold text-slate-800 line-clamp-2 leading-snug">{{ $activity->title }}</h3>
        <p class="mt-1 text-sm text-slate-500 line-clamp-2">{{ $activity->summary }}</p>
    </div>
    <div class="px-4 py-3 border-t border-stone-100 text-xs text-slate-500 flex items-center justify-between">
        <span>📍 {{ $activity->municipality->name }}</span>
        @if ($activity->application_deadline)
            <span>締切 {{ $activity->application_deadline->format('n/j') }}</span>
        @elseif ($activity->start_at)
            <span>{{ $activity->start_at->format('n/j') }}</span>
        @endif
    </div>
</a>
