@extends('layouts.admin')

@section('title', '監査ログ')

@section('content')
    <div class="bg-white rounded-xl border border-stone-200 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-slate-500 border-b border-stone-100">
                <tr><th class="px-4 py-2">日時</th><th class="px-4 py-2">ユーザー</th><th class="px-4 py-2">操作</th><th class="px-4 py-2">対象</th><th class="px-4 py-2">IP</th></tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr class="border-b border-stone-50">
                        <td class="px-4 py-3 text-slate-400 whitespace-nowrap">{{ $log->created_at->format('Y/n/j H:i:s') }}</td>
                        <td class="px-4 py-3">{{ $log->user?->name ?? 'システム' }}</td>
                        <td class="px-4 py-3"><span class="px-2 py-0.5 rounded bg-stone-100 text-xs">{{ $log->action }}</span></td>
                        <td class="px-4 py-3 text-slate-500">{{ $log->target_type }}{{ $log->target_id ? ' #'.$log->target_id : '' }}</td>
                        <td class="px-4 py-3 text-slate-400">{{ $log->ip_address }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-slate-400">ログがありません。</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $logs->links() }}</div>
@endsection
