<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\OutboundClick;
use Illuminate\Http\Request;

/**
 * 一次情報への送客＋クリック計測（PUB-006 / ACC-003）。
 * リダイレクト経由でクリックを記録し、活動の累計を加算する。
 */
class OutboundController extends Controller
{
    public function redirect(Request $request, Activity $activity)
    {
        abort_unless($activity->status === 'published', 404);

        $type = $request->input('type') === 'apply' ? 'apply' : 'source';
        $target = $type === 'apply' ? ($activity->apply_url ?: $activity->source_url) : $activity->source_url;

        abort_if(blank($target), 404);

        OutboundClick::create([
            'activity_id' => $activity->id,
            'municipality_id' => $activity->municipality_id,
            'target_url' => $target,
            'link_type' => $type,
            'referrer' => $request->headers->get('referer'),
            'clicked_at' => now(),
        ]);

        $activity->increment('click_count');

        return redirect()->away($target);
    }
}
