<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExtractionRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'crawl_run_id', 'activity_id', 'model', 'prompt_version',
        'source_text', 'extracted', 'confidence',
        'review_status', 'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        'extracted' => 'array',
        'confidence' => 'float',
        'reviewed_at' => 'datetime',
    ];

    public function crawlRun(): BelongsTo
    {
        return $this->belongsTo(CrawlRun::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
