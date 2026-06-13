@extends('layouts.admin')

@section('title', 'ダッシュボード')

@section('content')
    <div class="grid grid-cols-2 lg:grid-cols-6 gap-4">
        @php
            $cards = [
                ['公開中の活動', $stats['published'], 'text-brand', route('admin.activities.index', ['status' => 'published'])],
                ['確認待ち', $stats['review'], 'text-amber-600', route('admin.activities.index', ['status' => 'review'])],
                ['期限切れ候補', $stats['expired'], 'text-orange-600', route('admin.review.index')],
                ['取得失敗', $stats['crawl_failed'], 'text-red-600', route('admin.review.index')],
                ['修正・削除依頼', $stats['corrections'], 'text-rose-600', route('admin.corrections.index')],
                ['送客クリック累計', $stats['clicks'], 'text-slate-700', null],
            ];
        @endphp
        @foreach ($cards as [$label, $value, $color, $url])
            <a @if($url) href="{{ $url }}" @endif class="bg-white rounded-xl border border-stone-200 p-5 block hover:shadow-sm">
                <div class="text-xs text-slate-500">{{ $label }}</div>
                <div class="mt-1 text-3xl font-bold {{ $color }}">{{ number_format($value) }}</div>
            </a>
        @endforeach
    </div>

    <p class="mt-2 text-xs text-slate-500">直近30日の送客クリック：{{ number_format($clicks30d) }}</p>

    {{-- 情報品質KPI（14-1） --}}
    <div class="mt-6 bg-white rounded-xl border border-stone-200 p-5">
        <h2 class="font-bold text-slate-700 mb-4 text-sm">情報品質KPI</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <div class="text-xs text-slate-500">公開中の有効活動数</div>
                <div class="mt-1 text-2xl font-bold text-brand">{{ number_format($kpi['active']) }}</div>
                <div class="text-xs text-slate-400">目標 100〜200件</div>
            </div>
            <div>
                <div class="text-xs text-slate-500">30日以内確認率</div>
                <div class="mt-1 text-2xl font-bold {{ ($kpi['verified_rate'] ?? 0) >= 80 ? 'text-accent-dark' : 'text-amber-600' }}">{{ $kpi['verified_rate'] === null ? '—' : $kpi['verified_rate'].'%' }}</div>
                <div class="text-xs text-slate-400">目標 80%以上</div>
            </div>
            <div>
                <div class="text-xs text-slate-500">期限切れ残存率</div>
                <div class="mt-1 text-2xl font-bold {{ ($kpi['expired_rate'] ?? 0) < 5 ? 'text-accent-dark' : 'text-red-600' }}">{{ $kpi['expired_rate'] === null ? '—' : $kpi['expired_rate'].'%' }}</div>
                <div class="text-xs text-slate-400">目標 5%未満</div>
            </div>
            <div>
                <div class="text-xs text-slate-500">公開中の活動（全体）</div>
                <div class="mt-1 text-2xl font-bold text-slate-700">{{ number_format($kpi['published']) }}</div>
            </div>
        </div>
    </div>

    <div class="mt-8 bg-white rounded-xl border border-stone-200">
        <div class="px-5 py-3 border-b border-stone-200 flex items-center justify-between">
            <h2 class="font-bold text-slate-700">確認待ちの活動</h2>
            <a href="{{ route('admin.activities.create') }}" class="text-sm px-3 py-1.5 rounded-lg bg-brand text-white">＋ 活動を追加</a>
        </div>
        <table class="w-full text-sm">
            <thead class="text-left text-slate-500 border-b border-stone-100">
                <tr><th class="px-5 py-2">活動名</th><th class="px-5 py-2">自治体</th><th class="px-5 py-2">更新</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($recentReview as $a)
                    <tr class="border-b border-stone-50 hover:bg-stone-50">
                        <td class="px-5 py-3 font-medium">{{ $a->title }}</td>
                        <td class="px-5 py-3 text-slate-500">{{ $a->municipality->name }}</td>
                        <td class="px-5 py-3 text-slate-400">{{ $a->updated_at->diffForHumans() }}</td>
                        <td class="px-5 py-3 text-right"><a href="{{ route('admin.activities.edit', $a) }}" class="text-brand hover:underline">編集</a></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-8 text-center text-slate-400">確認待ちの活動はありません。</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
