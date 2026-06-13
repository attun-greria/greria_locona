<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Municipality;

/**
 * 自治体ページ（PUB-007 / PUB-04）。
 * 自治体概要・関連リンク・公開中の活動・LINE導線・相談窓口を表示する。
 */
class MunicipalityController extends Controller
{
    public function index()
    {
        $municipalities = Municipality::where('is_published', true)
            ->withCount(['activities' => fn ($q) => $q->published()->notExpired()])
            ->orderBy('prefecture')->orderBy('name')
            ->paginate(24);

        return view('public.municipalities.index', compact('municipalities'));
    }

    public function show(Municipality $municipality)
    {
        abort_unless($municipality->is_published, 404);

        $activities = $municipality->activities()
            ->published()->notExpired()
            ->with('category')
            ->latest('verified_at')
            ->paginate(12);

        return view('public.municipalities.show', compact('municipality', 'activities'));
    }
}
