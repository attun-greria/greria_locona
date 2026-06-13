<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CorrectionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_id', 'municipality_id', 'requester_name', 'requester_email',
        'type', 'message', 'status', 'admin_note', 'ip_address',
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class);
    }

    public function typeLabel(): string
    {
        return ['correction' => '修正依頼', 'deletion' => '削除依頼', 'other' => 'その他'][$this->type] ?? $this->type;
    }

    public function statusLabel(): string
    {
        return ['open' => '未対応', 'in_progress' => '対応中', 'resolved' => '対応済み', 'rejected' => '却下'][$this->status] ?? $this->status;
    }
}
