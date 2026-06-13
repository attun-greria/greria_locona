<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>ログイン｜LOCONA 運用管理</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{brand:{DEFAULT:'#2C6E6A',dark:'#234E4B'},accent:{DEFAULT:'#9CCC4F',dark:'#7BB534'}}}}}</script>
</head>
<body class="bg-stone-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-sm">
        <div class="text-center mb-6">
            <span class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-brand text-white font-bold text-2xl">L</span>
            <div class="mt-2 text-xl font-bold text-brand-dark">LOCONA<span class="text-accent">.</span> 運用管理</div>
        </div>

        <form method="post" action="{{ route('admin.login') }}" class="bg-white rounded-xl shadow p-6 space-y-4">
            @csrf
            @if ($errors->any())
                <div class="px-3 py-2 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">{{ $errors->first() }}</div>
            @endif
            <div>
                <label class="block text-sm font-semibold mb-1">メールアドレス</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:ring-2 focus:ring-brand outline-none">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">パスワード</label>
                <input type="password" name="password" required
                       class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:ring-2 focus:ring-brand outline-none">
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="remember" value="1" class="rounded text-brand"> ログイン状態を保持
            </label>
            <button class="w-full py-2.5 rounded-lg bg-brand text-white font-bold hover:bg-brand-dark transition">ログイン</button>
        </form>
    </div>
</body>
</html>
