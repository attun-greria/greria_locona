<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Municipality;
use Illuminate\Http\Response;

/**
 * SEO: sitemap.xml / robots.txt（NFR-011 / PUB-010）。
 * 公開ページをクロール可能にし、管理画面は除外する。
 */
class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $urls = [
            ['loc' => route('home'), 'priority' => '1.0', 'changefreq' => 'daily'],
            ['loc' => route('activities.index'), 'priority' => '0.9', 'changefreq' => 'daily'],
            ['loc' => route('municipalities.index'), 'priority' => '0.7', 'changefreq' => 'weekly'],
        ];

        Municipality::where('is_published', true)->get()->each(function ($m) use (&$urls) {
            $urls[] = [
                'loc' => route('municipalities.show', $m),
                'lastmod' => $m->updated_at?->toAtomString(),
                'priority' => '0.6', 'changefreq' => 'weekly',
            ];
        });

        Activity::published()->notExpired()->get()->each(function ($a) use (&$urls) {
            $urls[] = [
                'loc' => route('activities.show', $a),
                'lastmod' => ($a->verified_at ?? $a->updated_at)?->toAtomString(),
                'priority' => '0.8', 'changefreq' => 'weekly',
            ];
        });

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($urls as $u) {
            $xml .= '  <url><loc>'.e($u['loc']).'</loc>';
            if (! empty($u['lastmod'])) {
                $xml .= '<lastmod>'.$u['lastmod'].'</lastmod>';
            }
            $xml .= '<changefreq>'.$u['changefreq'].'</changefreq>';
            $xml .= '<priority>'.$u['priority'].'</priority></url>'."\n";
        }
        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /go/',     // 送客リダイレクトはクロール対象外
            'Allow: /',
            'Sitemap: '.url('/sitemap.xml'),
        ];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain']);
    }
}
