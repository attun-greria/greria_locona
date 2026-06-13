<?php

namespace App\Support;

use App\Models\Activity;
use Illuminate\Support\Collection;

/**
 * 重複候補の検出（ADM-009 / CRW-011）。
 * 同一URL・同一自治体×類似タイトル・同一自治体×同一開催日 から候補グループを生成する。
 */
class DuplicateFinder
{
    /** タイトル類似とみなす閾値（%） */
    public const TITLE_SIMILARITY = 82.0;

    /**
     * @return array<int, array{reason:string, activities:Collection}>
     */
    public function candidates(): array
    {
        $activities = Activity::query()
            ->whereIn('status', ['draft', 'review', 'published'])
            ->with('municipality')
            ->get(['id', 'title', 'slug', 'municipality_id', 'start_at', 'source_url', 'status']);

        $groups = [];
        $seenPairs = [];

        // 1. 同一 source_url
        foreach ($activities->groupBy('source_url') as $url => $items) {
            if ($url && $items->count() > 1) {
                $groups[] = ['reason' => '同一の一次情報URL', 'activities' => $items->values()];
                foreach ($items as $a) {
                    $seenPairs[$a->id] = true;
                }
            }
        }

        // 2/3. 自治体ごとにバケット化し、類似タイトル / 同一開催日 を比較
        foreach ($activities->groupBy('municipality_id') as $bucket) {
            $list = $bucket->values();
            $count = $list->count();
            for ($i = 0; $i < $count; $i++) {
                for ($j = $i + 1; $j < $count; $j++) {
                    $a = $list[$i];
                    $b = $list[$j];

                    // 既に同一URLグループで拾った組み合わせはスキップ
                    if ($a->source_url && $a->source_url === $b->source_url) {
                        continue;
                    }

                    $reason = null;
                    if ($this->titleSimilarity($a->title, $b->title) >= self::TITLE_SIMILARITY) {
                        $reason = '類似タイトル（同一自治体）';
                    } elseif ($a->start_at && $b->start_at && $a->start_at->isSameDay($b->start_at)) {
                        $reason = '同一開催日（同一自治体）';
                    }

                    if ($reason) {
                        $groups[] = ['reason' => $reason, 'activities' => collect([$a, $b])];
                    }
                }
            }
        }

        return $groups;
    }

    private function titleSimilarity(string $a, string $b): float
    {
        $a = $this->normalize($a);
        $b = $this->normalize($b);
        if ($a === '' || $b === '') {
            return 0.0;
        }
        if ($a === $b) {
            return 100.0;
        }
        similar_text($a, $b, $percent);

        return $percent;
    }

    private function normalize(string $title): string
    {
        // 全角空白・記号・（複製）等の揺れを除去して比較精度を上げる
        $title = preg_replace('/（複製）|\(複製\)/u', '', $title) ?? $title;

        return trim(preg_replace('/[\s　]+/u', '', $title) ?? $title);
    }
}
