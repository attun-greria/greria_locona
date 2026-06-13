<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>@yield('title', '管理画面')｜LOCONA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-stone-100 text-slate-800 antialiased">
<div class="flex min-h-screen">
    {{-- サイドバー --}}
    <aside class="w-60 bg-brand-dark text-stone-200 flex-shrink-0 hidden md:flex flex-col">
        <div class="h-16 flex items-center px-5 border-b border-white/10">
            <span class="text-lg font-bold text-white">LOCONA<span class="text-accent">.</span></span>
            <span class="ml-2 text-xs text-stone-400">運用管理</span>
        </div>
        @php
            $nav = [
                ['admin.dashboard', 'ダッシュボード', '📊'],
                ['admin.activities.index', '活動', '🌱'],
                ['admin.municipalities.index', '自治体', '🏛'],
                ['admin.sources.index', '収集元URL', '🔗'],
                ['admin.extractions.index', '抽出レビュー', '🤖'],
                ['admin.review.index', '期限切れ・失敗', '⏰'],
                ['admin.duplicates.index', '重複候補', '👯'],
                ['admin.corrections.index', '修正・削除依頼', '✉️'],
                ['admin.categories.index', 'カテゴリ', '🏷'],
                ['admin.tags.index', 'タグ', '#️⃣'],
            ];
        @endphp
        <nav class="flex-1 py-4 space-y-1 text-sm">
            @foreach ($nav as [$route, $label, $icon])
                <a href="{{ route($route) }}"
                   class="flex items-center gap-3 px-5 py-2.5 hover:bg-white/10 {{ request()->routeIs(str_replace('.index','',$route).'*') ? 'bg-white/10 border-l-2 border-accent text-white font-semibold' : '' }}">
                    <span>{{ $icon }}</span><span>{{ $label }}</span>
                </a>
            @endforeach
            @if (auth()->user()?->isAdmin())
                <a href="{{ route('admin.audit-logs.index') }}"
                   class="flex items-center gap-3 px-5 py-2.5 hover:bg-white/10 {{ request()->routeIs('admin.audit-logs*') ? 'bg-white/10 border-l-2 border-accent text-white font-semibold' : '' }}">
                    <span>📝</span><span>監査ログ</span>
                </a>
            @endif
        </nav>
    </aside>

    <div class="flex-1 flex flex-col min-w-0">
        <header class="h-16 bg-white border-b border-stone-200 flex items-center justify-between px-6">
            <h1 class="font-bold text-brand-dark">@yield('title', '管理画面')</h1>
            <div class="flex items-center gap-4 text-sm">
                <a href="{{ route('home') }}" target="_blank" class="text-slate-500 hover:text-brand">公開サイト ↗</a>
                <span class="text-slate-600">{{ auth()->user()?->name }}<span class="ml-1 text-xs px-1.5 py-0.5 rounded bg-stone-100 text-slate-500">{{ auth()->user()?->role }}</span></span>
                <form method="post" action="{{ route('admin.logout') }}">@csrf
                    <button class="text-slate-500 hover:text-red-600">ログアウト</button>
                </form>
            </div>
        </header>

        <main class="flex-1 p-6">
            @if (session('status'))
                <div class="mb-4 px-4 py-3 rounded-lg bg-accent/15 border border-accent text-accent-dark text-sm">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
                    <ul class="list-disc list-inside">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
