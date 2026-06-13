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
        'municipality_id', 'url', 'page_type', 'crawl_frequency', 'priority', 'is_active',
        'robots_checked', 'terms_checked', 'terms_note',
        'last_crawled_at', 'last_content_hash', 'failure_count',
    ];

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
