<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * タグ管理（ADM-006 / 9-1 LINEタグ）。
 * 検索用タグとLINE運用タグを type で区別する。
 */
class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::withCount('activities')->orderBy('type')->orderBy('name')->get()->groupBy('type');

        return view('admin.tags.index', compact('tags'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:search,line,ops'],
        ]);
        $data['slug'] = Str::slug($data['name']) ?: 't-'.Str::lower(Str::random(6));

        $tag = Tag::create($data);
        Audit::log('created', $tag, $data);

        return back()->with('status', 'タグを追加しました。');
    }

    public function destroy(Tag $tag)
    {
        Audit::log('deleted', $tag);
        $tag->delete();

        return back()->with('status', 'タグを削除しました。');
    }
}
