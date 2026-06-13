@extends('layouts.admin')

@section('title', 'CSVインポート')

@section('content')
<div class="max-w-xl space-y-6">
    <div class="bg-white rounded-xl border border-stone-200 p-5">
        <h2 class="font-bold text-slate-700 text-sm mb-3">自治体CSVインポート</h2>
        <p class="text-sm text-slate-500 mb-4">
            ヘッダ行：<code class="bg-stone-100 px-1 rounded">name, prefecture, city, summary, official_url, line_url, is_published</code><br>
            <code>name</code> が一致する自治体は更新、無ければ新規作成します。
        </p>
        <form method="post" action="{{ route('admin.csv.import.municipalities') }}" enctype="multipart/form-data" class="space-y-3">
            @csrf
            <input type="file" name="file" accept=".csv,text/csv" required class="block w-full text-sm border border-stone-300 rounded-lg p-2">
            <button class="px-5 py-2 rounded-lg bg-brand text-white font-bold">インポート</button>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-stone-200 p-5">
        <h2 class="font-bold text-slate-700 text-sm mb-3">エクスポート</h2>
        <div class="flex gap-3">
            <a href="{{ route('admin.csv.municipalities') }}" class="px-4 py-2 rounded-lg border border-stone-300 text-sm hover:border-brand">自治体CSV</a>
            <a href="{{ route('admin.csv.activities') }}" class="px-4 py-2 rounded-lg border border-stone-300 text-sm hover:border-brand">活動CSV</a>
        </div>
    </div>
</div>
@endsection
