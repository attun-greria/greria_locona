<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 公開API用の活動表現（11-1 GET /api/activities）。
 * 個人情報は含めず、公開済みの事実情報と一次情報URLのみを返す。
 */
class ActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'summary' => $this->summary,
            'category' => $this->whenLoaded('category', fn () => $this->category?->name),
            'municipality' => $this->whenLoaded('municipality', fn () => [
                'slug' => $this->municipality->slug,
                'name' => $this->municipality->name,
                'prefecture' => $this->municipality->prefecture,
            ]),
            'organizer' => $this->organizer_name,
            'application_deadline' => optional($this->application_deadline)->toDateString(),
            'start_at' => optional($this->start_at)->toIso8601String(),
            'end_at' => optional($this->end_at)->toIso8601String(),
            'is_recurring' => $this->is_recurring,
            'fee_text' => $this->fee_text,
            'conditions' => [
                'child_friendly' => $this->child_friendly,
                'beginner_friendly' => $this->beginner_friendly,
                'online_available' => $this->online_available,
                'has_reward' => $this->has_reward,
                'transport_support' => $this->transport_support,
                'lodging_support' => $this->lodging_support,
            ],
            'source_url' => $this->source_url,
            'verified_at' => optional($this->verified_at)->toDateString(),
            'url' => route('activities.show', $this->resource),
        ];
    }
}
