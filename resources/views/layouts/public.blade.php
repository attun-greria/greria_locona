<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1"><!-- スマホ優先 NFR-009 -->
    {{-- SEO: PUB-010 --}}
    <title>@yield('title', 'LOCONA｜地域への「よりみち」を見つけ、つながりを育てる。')</title>
    <meta name="description" content="@yield('meta_description', '関係人口データ基盤・自治体向けLINE運用支援サービス。地域、テーマ、時期、参加条件から地域活動を探せます。')">
    @hasSection('canonical')<link rel="canonical" href="@yield('canonical')">@endif
    {{-- OGP --}}
    <meta property="og:site_name" content="LOCONA">
    <meta property="og:title" content="@yield('title', 'LOCONA')">
    <meta property="og:description" content="@yield('meta_description', '地域への「よりみち」を見つけ、つながりを育てる。')">
    <meta property="og:type" content="website">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="bg-stone-50 text-slate-800 antialiased flex flex-col min-h-screen">
    <header class="bg-white border-b border-stone-200 sticky top-0 z-30">
        <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-brand text-white font-bold text-xl">L</span>
                <span class="text-xl font-bold tracking-wide text-brand-dark">LOCONA<span class="text-accent">.</span></span>
            </a>
            <nav class="flex items-center gap-4 text-sm font-medium">
                <a href="{{ route('activities.index') }}" class="hover:text-brand">活動をさがす</a>
                <a href="{{ route('municipalities.index') }}" class="hover:text-brand">自治体から探す</a>
            </nav>
        </div>
    </header>

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="bg-brand-dark text-stone-200 mt-16">
        <div class="max-w-6xl mx-auto px-4 py-10 text-sm">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="text-lg font-bold text-white">LOCONA<span class="text-accent">.</span></div>
                    <p class="mt-1 text-stone-300">地域への「よりみち」を見つけ、つながりを育てる。</p>
                </div>
                <nav class="flex gap-6">
                    <a href="{{ route('activities.index') }}" class="hover:text-accent">活動一覧</a>
                    <a href="{{ route('municipalities.index') }}" class="hover:text-accent">自治体一覧</a>
                </nav>
            </div>
            <p class="mt-8 text-xs text-stone-400">本サービスは公開情報を整理し、詳細・申込は各一次情報へご案内します。&copy; {{ date('Y') }} 株式会社Greria</p>
        </div>
    </footer>
</body>
</html>
