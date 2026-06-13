@extends('layouts.public')

@section('title', '活動をさがす｜LOCONA')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-brand-dark mb-6">活動をさがす</h1>

    <div class="grid lg:grid-cols-4 gap-6">
        {{-- 絞り込み（PUB-003/004） --}}
        <aside class="lg:col-span-1">
            <form action="{{ route('activities.index') }}" method="get" class="bg-white border border-stone-200 rounded-xl p-4 space-y-4 text-sm">
                <div>
                    <label class="block font-semibold mb-1">キーワード</label>
                    <input type="text" name="q" value="{{ request('q') }}" class="w-full px-3 py-2 border border-stone-300 rounded-lg">
                </div>
                <div>
                    <label class="block font-semibold mb-1">都道府県</label>
                    <select name="prefecture" class="w-full px-3 py-2 border border-stone-300 rounded-lg">
                        <option value="">すべて</option>
                        @foreach ($prefectures as $pref)
                            <option value="{{ $pref }}" @selected(request('prefecture') === $pref)>{{ $pref }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-semibold mb-1">種別</label>
                    <select name="kind" class="w-full px-3 py-2 border border-stone-300 rounded-lg">
                        <option value="">すべて</option>
                        @foreach (['event' => '活動・イベント', 'program' => '制度・支援', 'intro' => '相談・紹介'] as $k => $v)
                            <option value="{{ $k }}" @selected(request('kind') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-semibold mb-1">カテゴリ</label>
                    <select name="category" class="w-full px-3 py-2 border border-stone-300 rounded-lg">
                        <option value="">すべて</option>
                        @foreach ($categories as $c)
                            <option value="{{ $c->slug }}" @selected(request('category') === $c->slug)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block font-semibold mb-1">時期(開始)</label>
                        <input type="date" name="from" value="{{ request('from') }}" class="w-full px-2 py-2 border border-stone-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">時期(終了)</label>
                        <input type="date" name="to" value="{{ request('to') }}" class="w-full px-2 py-2 border border-stone-300 rounded-lg">
                    </div>
                </div>
                <div>
                    <span class="block font-semibold mb-2">参加条件</span>
                    <div class="space-y-1.5">
                        @foreach ([
                            'child_friendly' => '子連れ可',
                            'beginner_friendly' => '初心者可',
                            'online_available' => 'オンライン可',
                            'has_reward' => '報酬あり',
                            'transport_support' => '交通費支援',
                            'lodging_support' => '宿泊支援',
                        ] as $key => $label)
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="{{ $key }}" value="1" @checked(request()->boolean($key)) class="rounded text-brand focus:ring-brand">
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="block font-semibold mb-1">開催形態</label>
                    <select name="recurrence" class="w-full px-3 py-2 border border-stone-300 rounded-lg">
                        <option value="">すべて</option>
                        <option value="single" @selected(request('recurrence')==='single')>単発</option>
                        <option value="recurring" @selected(request('recurrence')==='recurring')>継続・定期</option>
                    </select>
                </div>
                <button class="w-full py-2.5 rounded-lg bg-brand text-white font-bold hover:bg-brand-dark transition">この条件でさがす</button>
                <a href="{{ route('activities.index') }}" class="block text-center text-slate-500 hover:underline">条件をクリア</a>
            </form>
        </aside>

        {{-- 結果 --}}
        <div class="lg:col-span-3">
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm text-slate-500">{{ number_format($activities->total()) }}件の活動</p>
                <form method="get" class="text-sm">
                    @foreach (request()->except('sort', 'page') as $k => $v)
                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                    @endforeach
                    <select name="sort" onchange="this.form.submit()" class="px-3 py-2 border border-stone-300 rounded-lg bg-white">
                        <option value="latest" @selected(request('sort')==='latest')>新着順</option>
                        <option value="deadline" @selected(request('sort')==='deadline')>締切が近い順</option>
                        <option value="popular" @selected(request('sort')==='popular')>人気順</option>
                    </select>
                </form>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                @each('public.activities._card', $activities, 'activity', 'public.activities._empty')
            </div>

            <div class="mt-6">{{ $activities->links() }}</div>
        </div>
    </div>
</div>
@endsection
