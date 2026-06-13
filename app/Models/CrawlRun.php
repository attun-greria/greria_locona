<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CrawlRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_id', 'http_status', 'content_hash', 'charset', 'snapshot_path',
        'content_type', 'result', 'diff_status', 'error_message', 'fetched_at',
    ];

    protected $casts = [
        'fetched_at' => 'datetime',
    ];

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    public function extractionRuns(): HasMany
    {
        return $this->hasMany(ExtractionRun::class);
    }
}
