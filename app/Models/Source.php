<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Source extends Model
{
    use HasFactory;

    protected $fillable = [
        'municipality_id', 'url', 'page_type', 'license_tier', 'publication_policy',
        'attribution_name', 'license_url', 'crawl_frequency', 'priority', 'is_active',
        'robots_checked', 'terms_checked', 'terms_note',
        'last_crawled_at', 'last_content_hash', 'failure_count',
    ];

    public function licenseTierLabel(): string
    {
        return [
            'open' => '公式・オープンデータ',
            'semi_public' => '観光協会・移住ポータル等',
            'restricted' => '民間・SNS',
        ][$this->license_tier] ?? $this->license_tier;
    }

    public function publicationPolicyLabel(): string
    {
        return [
            'publishable' => '公開可（事実＋編集＋出典）',
            'link_only' => 'リンク送客のみ',
            'internal_only' => '社内診断のみ（非公開）',
        ][$this->publication_policy] ?? $this->publication_policy;
    }

    /** この収集元由来の活動の既定可視性（internal_only は社内のみ） */
    public function defaultVisibility(): string
    {
        return $this->publication_policy === 'internal_only' ? 'internal' : 'public';
    }

    protected $casts = [
        'is_active' => 'boolean',
        'robots_checked' => 'boolean',
        'terms_checked' => 'boolean',
        'last_crawled_at' => 'datetime',
    ];

    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class);
    }

    public function crawlRuns(): HasMany
    {
        return $this->hasMany(CrawlRun::class);
    }
}
