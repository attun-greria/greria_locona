<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityCategory;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * カテゴリ管理（ADM-006 / ADM-08）。
 */
class CategoryController extends Controller
{
    public function index()
    {
        $categories = ActivityCategory::withCount('activities')
            ->orderBy('display_order')->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:32'],
            'display_order' => ['nullable', 'integer'],
        ]);
        $data['slug'] = Str::slug($data['name']) ?: 'c-'.Str::lower(Str::random(6));
        $data['display_order'] ??= 0;

        $category = ActivityCategory::create($data);
        Audit::log('created', $category, $data);

        return back()->with('status', 'カテゴリを追加しました。');
    }

    public function update(Request $request, ActivityCategory $category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:32'],
            'display_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $category->update($data);
        Audit::log('updated', $category, $data);

        return back()->with('status', 'カテゴリを更新しました。');
    }

    public function destroy(ActivityCategory $category)
    {
        Audit::log('deleted', $category);
        $category->delete();

        return back()->with('status', 'カテゴリを削除しました。');
    }
}
