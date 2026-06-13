<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LineWebhookEvent extends Model
{
    public const UPDATED_AT = null;
    public const CREATED_AT = null;

    protected $fillable = [
        'line_channel_id', 'line_user_id', 'event_type', 'line_user_ref', 'payload', 'received_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'received_at' => 'datetime',
    ];

    public function channel(): BelongsTo
    {
        return $this->belongsTo(LineChannel::class, 'line_channel_id');
    }
}
