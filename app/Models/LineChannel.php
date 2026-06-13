<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LineChannel extends Model
{
    use HasFactory;

    protected $fillable = [
        'municipality_id', 'channel_name', 'channel_id', 'secret_ref', 'webhook_path', 'status',
    ];

    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class);
    }

    public function lineUsers(): HasMany
    {
        return $this->hasMany(LineUser::class);
    }
}
