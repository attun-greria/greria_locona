<?php

namespace App\Services\Extraction;

use App\Models\Activity;
use App\Models\ExtractionRun;
use Illuminate\Support\Str;

/**
 * 採用された抽出結果から活動ドラフトを生成する（5-1 公開判定への橋渡し / ADM-008→ADM-004）。
 * 生成される活動は status=draft。必ず人が内容を確認・補完してから公開する。
 */
class ActivityDraftFactory
{
    /**
     * @return Activity|null  自治体が特定できない場合は null（手動設定を促す）
     */
    public function fromExtraction(ExtractionRun $extraction): ?Activity
    {
        $extraction->loadMissing('crawlRun.source');
        $source = $extraction->crawlRun?->source;
        $municipalityId = $source?->municipality_id;

        if (! $municipalityId) {
            return null; // 収集元に自治体が紐づいていない → 自動生成しない
        }

        $data = $extraction->extracted ?? [];
        $title = $data['title'] ?? ('抽出活動 #'.$extraction->id);

        $activity = new Activity([
            'municipality_id' => $municipalityId,
            'title' => $title,
            'summary' => $data['summary'] ?? Str::limit(strip_tags((string) ($extraction->source_text ?? '')), 200),
            'source_url' => $data['source_url'] ?? $source->url,
            'application_deadline' => $this->date($data['application_deadline'] ?? null),
            'start_at' => $this->dateTime($data['start_at'] ?? null),
            'end_at' => $this->dateTime($data['end_at'] ?? null),
            'fee_text' => $data['fee_text'] ?? null,
            'status' => 'draft',
            'extraction_confidence' => $extraction->confidence,
        ]);

        foreach (['child_friendly', 'beginner_friendly', 'online_available', 'has_reward', 'transport_support', 'lodging_support'] as $flag) {
            $activity->{$flag} = (bool) ($data[$flag] ?? false);
        }

        $activity->slug = $this->uniqueSlug($title);
        $activity->save();

        return $activity;
    }

    private function date(?string $value): ?string
    {
        return $value ? (strtotime($value) ? date('Y-m-d', strtotime($value)) : null) : null;
    }

    private function dateTime(?string $value): ?string
    {
        return $value ? (strtotime($value) ? date('Y-m-d H:i:s', strtotime($value)) : null) : null;
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'a-'.Str::lower(Str::random(6));
        $slug = $base;
        $i = 1;
        while (Activity::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
