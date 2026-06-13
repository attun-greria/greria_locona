<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MunicipalityResource;
use App\Models\Municipality;

/**
 * 公開自治体API（11-1: GET /api/municipalities, GET /api/municipalities/{id}）。
 */
class MunicipalityController extends Controller
{
    public function index()
    {
        $municipalities = Municipality::where('is_published', true)
            ->withCount(['activities' => fn ($q) => $q->published()->notExpired()])
            ->orderBy('prefecture')->orderBy('name')
            ->paginate(50);

        return MunicipalityResource::collection($municipalities);
    }

    public function show(Municipality $municipality)
    {
        abort_unless($municipality->is_published, 404);
        $municipality->loadCount(['activities' => fn ($q) => $q->published()->notExpired()]);

        return new MunicipalityResource($municipality);
    }
}
