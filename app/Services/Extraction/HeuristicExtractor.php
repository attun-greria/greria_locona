<?php

namespace App\Services\Extraction;

use App\Services\Extraction\Contracts\Extractor;

/**
 * ローカル・ヒューリスティック抽出（既定・外部送信なし / SEC-010）。
 *
 * HTMLの title / meta description / OGP / JSON-LD(Event) / 本文中の日付・費用表現から
 * 共通スキーマ候補を生成する。LLMを使わないため精度は限定的だが、
 * 「自動抽出 → 人による確認」フロー（5-1）の自動側を外部送信なしで成立させる。
 * 抽出結果は必ず人手レビュー（ADM-008）を経て公開される。
 */
class HeuristicExtractor implements Extractor
{
    public function extract(string $content, string $url): ExtractionResult
    {
        $text = $this->stripTags($content);
        $data = [];
        $hits = 0;

        // JSON-LD Event を最優先
        $jsonLd = $this->parseJsonLdEvent($content);
        if ($jsonLd) {
            $data = array_merge($data, $jsonLd);
            $hits += count($jsonLd);
        }

        // title / og:title
        if (! isset($data['title'])) {
            $title = $this->meta($content, 'og:title') ?? $this->tag($content, 'title');
            if ($title) {
                $data['title'] = $this->clean($title);
                $hits++;
            }
        }

        // 要約: meta description / og:description
        $desc = $this->meta($content, 'og:description') ?? $this->metaName($content, 'description');
        if ($desc) {
            $data['summary'] = $this->clean($desc);
            $hits++;
        }

        // 申込締切・開催日（日本語の日付表現）
        if (! isset($data['application_deadline']) && ($deadline = $this->findDeadline($text))) {
            $data['application_deadline'] = $deadline;
            $hits++;
        }
        if (! isset($data['start_at']) && ($date = $this->findFirstDate($text))) {
            $data['start_at'] = $date;
            $hits++;
        }

        // 費用
        if ($fee = $this->findFee($text)) {
            $data['fee_text'] = $fee;
            $hits++;
        }

        // 参加条件フラグ（キーワード一致）
        $flags = $this->detectFlags($text);
        if ($flags) {
            $data = array_merge($data, $flags);
            $hits += count($flags);
        }

        $data['source_url'] = $url;

        // 信頼度：拾えた項目数に応じて段階付け（CRW-009）。人手確認の優先順位に利用。
        $confidence = match (true) {
            $jsonLd !== [] => min(0.9, 0.6 + 0.05 * $hits),
            $hits >= 4 => 0.7,
            $hits >= 2 => 0.5,
            default => 0.3,
        };

        return new ExtractionResult($data, round($confidence, 2), mb_substr($text, 0, 2000));
    }

    private function stripTags(string $html): string
    {
        $html = preg_replace('#<script.*?</script>#is', ' ', $html) ?? $html;
        $html = preg_replace('#<style.*?</style>#is', ' ', $html) ?? $html;

        return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($html))) ?? '');
    }

    private function tag(string $html, string $tag): ?string
    {
        return preg_match("#<{$tag}[^>]*>(.*?)</{$tag}>#is", $html, $m) ? $m[1] : null;
    }

    private function meta(string $html, string $property): ?string
    {
        return preg_match('#<meta[^>]+property=["\']'.preg_quote($property, '#').'["\'][^>]+content=["\'](.*?)["\']#is', $html, $m)
            ? $m[1] : null;
    }

    private function metaName(string $html, string $name): ?string
    {
        return preg_match('#<meta[^>]+name=["\']'.preg_quote($name, '#').'["\'][^>]+content=["\'](.*?)["\']#is', $html, $m)
            ? $m[1] : null;
    }

    private function parseJsonLdEvent(string $html): array
    {
        if (! preg_match_all('#<script[^>]+type=["\']application/ld\+json["\'][^>]*>(.*?)</script>#is', $html, $blocks)) {
            return [];
        }
        foreach ($blocks[1] as $raw) {
            $json = json_decode(trim($raw), true);
            if (! is_array($json)) {
                continue;
            }
            $candidates = isset($json['@type']) ? [$json] : $json;
            foreach ($candidates as $node) {
                if (! is_array($node) || ($node['@type'] ?? null) !== 'Event') {
                    continue;
                }
                $out = [];
                if (! empty($node['name'])) {
                    $out['title'] = $this->clean($node['name']);
                }
                if (! empty($node['description'])) {
                    $out['summary'] = $this->clean($node['description']);
                }
                if (! empty($node['startDate'])) {
                    $out['start_at'] = $node['startDate'];
                }
                if (! empty($node['endDate'])) {
                    $out['end_at'] = $node['endDate'];
                }

                return $out;
            }
        }

        return [];
    }

    private function findDeadline(string $text): ?string
    {
        if (preg_match('/(締切|申込締切|応募締切|締め切り)[：:\s]*'.'(\d{4})[年\/\-](\d{1,2})[月\/\-](\d{1,2})/u', $text, $m)) {
            return sprintf('%04d-%02d-%02d', $m[2], $m[3], $m[4]);
        }

        return null;
    }

    private function findFirstDate(string $text): ?string
    {
        if (preg_match('/(\d{4})[年\/\-](\d{1,2})[月\/\-](\d{1,2})/u', $text, $m)) {
            return sprintf('%04d-%02d-%02d', $m[1], $m[2], $m[3]);
        }

        return null;
    }

    private function findFee(string $text): ?string
    {
        if (preg_match('/(参加費|費用|料金)[：:\s]*([0-9,]+\s*円|無料)/u', $text, $m)) {
            return trim($m[2]);
        }
        if (preg_match('/(無料)/u', $text)) {
            return '無料';
        }

        return null;
    }

    private function detectFlags(string $text): array
    {
        $map = [
            'child_friendly' => ['子連れ', '親子', 'お子様', '子ども連れ'],
            'beginner_friendly' => ['初心者', '未経験', 'はじめて'],
            'online_available' => ['オンライン', 'リモート', 'Zoom', 'zoom'],
            'has_reward' => ['報酬', '謝礼', '有償'],
            'transport_support' => ['交通費'],
            'lodging_support' => ['宿泊', '宿あり', '宿泊費'],
        ];
        $out = [];
        foreach ($map as $flag => $keywords) {
            foreach ($keywords as $kw) {
                if (mb_strpos($text, $kw) !== false) {
                    $out[$flag] = true;
                    break;
                }
            }
        }

        return $out;
    }

    private function clean(string $s): string
    {
        return trim(preg_replace('/\s+/u', ' ', html_entity_decode($s)) ?? $s);
    }
}
