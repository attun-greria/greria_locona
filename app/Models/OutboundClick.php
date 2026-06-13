<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutboundClick extends Model
{
    public const UPDATED_AT = null;
    public const CREATED_AT = null;

    protected $fillable = [
        'activity_id', 'municipality_id', 'target_url', 'link_type', 'referrer', 'clicked_at',
    ];

    protected $casts = [
        'clicked_at' => 'datetime',
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class);
    }
}
