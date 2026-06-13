@extends('layouts.admin')

@section('title', '重複候補')

@section('content')
    <p class="text-sm text-slate-500 mb-4">同一URL・類似タイトル・同一開催日から重複の可能性がある活動を表示します（ADM-009）。内容を確認し、不要なものはアーカイブしてください。</p>

    @forelse ($groups as $group)
        <div class="bg-white rounded-xl border border-stone-200 mb-4 overflow-hidden">
            <div class="px-4 py-2 bg-amber-50 border-b border-amber-100 text-sm text-amber-800 font-medium">
                {{ $group['reason'] }}
            </div>
            <table class="w-full text-sm">
                <tbody>
                    @foreach ($group['activities'] as $a)
                        <tr class="border-b border-stone-50 hover:bg-stone-50">
                            <td class="px-4 py-3 font-medium">{{ $a->title }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $a->municipality?->name }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ optional($a->start_at)->format('y/n/j') ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-400 max-w-xs truncate">{{ $a->source_url }}</td>
                            <td class="px-4 py-3"><span class="text-xs px-2 py-0.5 rounded-full bg-stone-100">{{ $a->status }}</span></td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('admin.activities.edit', $a) }}" class="text-brand hover:underline">編集</a>
                                <form method="post" action="{{ route('admin.activities.status', $a) }}" class="inline">@csrf @method('PATCH')
                                    <input type="hidden" name="status" value="archived">
                                    <button class="ml-2 text-slate-500 hover:text-brand">アーカイブ</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @empty
        <div class="bg-white rounded-xl border border-dashed border-stone-300 p-10 text-center text-slate-400">
            重複候補は見つかりませんでした。
        </div>
    @endforelse
@endsection
