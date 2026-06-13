<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 公開API用の自治体表現（11-1 GET /api/municipalities）。
 */
class MunicipalityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'prefecture' => $this->prefecture,
            'city' => $this->city,
            'summary' => $this->summary,
            'official_url' => $this->official_url,
            'line_url' => $this->line_url,
            'activities_count' => $this->when(isset($this->activities_count), $this->activities_count),
            'url' => route('municipalities.show', $this->resource),
        ];
    }
}
