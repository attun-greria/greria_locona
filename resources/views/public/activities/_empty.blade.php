{{-- 検索結果なし（PUB-05） --}}
<div class="col-span-full bg-white border border-dashed border-stone-300 rounded-xl p-10 text-center">
    <div class="text-3xl">🔍</div>
    <p class="mt-3 font-semibold text-slate-700">条件に合う活動が見つかりませんでした</p>
    <p class="mt-1 text-sm text-slate-500">条件を変えるか、テーマや自治体から探してみてください。</p>
    <div class="mt-4 flex flex-wrap gap-2 justify-center">
        <a href="{{ route('activities.index') }}" class="px-4 py-2 rounded-lg bg-brand text-white text-sm">すべての活動を見る</a>
        <a href="{{ route('municipalities.index') }}" class="px-4 py-2 rounded-lg border border-stone-300 text-sm">自治体から探す</a>
    </div>
</div>
