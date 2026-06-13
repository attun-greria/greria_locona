<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityResource;
use App\Models\Activity;
use App\Support\ActivityFilter;
use Illuminate\Http\Request;

/**
 * 公開活動API（11-1: GET /api/activities, GET /api/activities/{id}）。
 * 認証不要。公開済みかつ期限内の活動のみ返す。
 */
class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::published()->notExpired()->with(['municipality', 'category']);
        ActivityFilter::apply($query, $request->all());

        return ActivityResource::collection($query->paginate(20)->withQueryString());
    }

    public function show(Activity $activity)
    {
        abort_unless($activity->status === 'published', 404);

        return new ActivityResource($activity->load(['municipality', 'category']));
    }
}
