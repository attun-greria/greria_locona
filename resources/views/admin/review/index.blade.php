@extends('layouts.admin')

@section('title', '期限切れ・取得失敗の確認')

@section('content')
    <h2 class="font-bold text-slate-700 mb-3">期限切れだが公開中の活動（期限切れ残存率）</h2>
    <div class="bg-white rounded-xl border border-stone-200 overflow-x-auto mb-8">
        <table class="w-full text-sm">
            <thead class="text-left text-slate-500 border-b border-stone-100">
                <tr><th class="px-4 py-2">活動名</th><th class="px-4 py-2">自治体</th><th class="px-4 py-2">締切</th><th class="px-4 py-2">開催終了</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($expired as $a)
                    <tr class="border-b border-stone-50 hover:bg-stone-50">
                        <td class="px-4 py-3 font-medium">{{ $a->title }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $a->municipality->name }}</td>
                        <td class="px-4 py-3 text-orange-600">{{ optional($a->application_deadline)->format('y/n/j') ?? '—' }}</td>
                        <td class="px-4 py-3 text-orange-600">{{ optional($a->end_at)->format('y/n/j') ?? '—' }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.activities.edit', $a) }}" class="text-brand hover:underline">編集</a>
                            <form method="post" action="{{ route('admin.activities.status', $a) }}" class="inline">@csrf @method('PATCH')
                                <input type="hidden" name="status" value="archived">
                                <button class="ml-2 text-slate-500 hover:text-brand">アーカイブ</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">期限切れの公開活動はありません。</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mb-8">{{ $expired->links() }}</div>

    <h2 class="font-bold text-slate-700 mb-3">取得失敗（クローラー）</h2>
    <div class="bg-white rounded-xl border border-stone-200 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-slate-500 border-b border-stone-100">
                <tr><th class="px-4 py-2">収集元URL</th><th class="px-4 py-2">自治体</th><th class="px-4 py-2">HTTP</th><th class="px-4 py-2">エラー</th><th class="px-4 py-2">取得日時</th></tr>
            </thead>
            <tbody>
                @forelse ($failedCrawls as $c)
                    <tr class="border-b border-stone-50">
                        <td class="px-4 py-3 max-w-xs truncate">{{ $c->source?->url }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $c->source?->municipality?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-red-600">{{ $c->http_status ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-500 max-w-xs truncate">{{ $c->error_message }}</td>
                        <td class="px-4 py-3 text-slate-400">{{ optional($c->fetched_at)->format('y/n/j H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">取得失敗はありません。</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
