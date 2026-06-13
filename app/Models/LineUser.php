<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LineUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'line_channel_id', 'line_user_id', 'consent', 'tags', 'follow_status', 'last_contact_at',
    ];

    protected $casts = [
        'consent' => 'boolean',
        'tags' => 'array',
        'last_contact_at' => 'datetime',
    ];

    public function channel(): BelongsTo
    {
        return $this->belongsTo(LineChannel::class, 'line_channel_id');
    }
}
