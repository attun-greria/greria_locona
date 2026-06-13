<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends Model
{
    use HasFactory;

    public const STATUSES = ['draft', 'review', 'published', 'archived', 'rejected'];

    protected $fillable = [
        'municipality_id', 'category_id', 'title', 'slug', 'summary', 'description',
        'source_url', 'apply_url', 'image_url', 'organizer_name',
        'application_deadline', 'start_at', 'end_at', 'is_recurring',
        'fee_text', 'child_friendly', 'beginner_friendly', 'online_available',
        'has_reward', 'transport_support', 'lodging_support', 'target_audience', 'capacity',
        'status', 'verified_at', 'extraction_confidence', 'meta_title', 'meta_description',
    ];

    protected $casts = [
        'application_deadline' => 'date',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'verified_at' => 'datetime',
        'is_recurring' => 'boolean',
        'child_friendly' => 'boolean',
        'beginner_friendly' => 'boolean',
        'online_available' => 'boolean',
        'has_reward' => 'boolean',
        'transport_support' => 'boolean',
        'lodging_support' => 'boolean',
        'extraction_confidence' => 'float',
    ];

    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ActivityCategory::class, 'category_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(ActivityRevision::class);
    }

    public function outboundClicks(): HasMany
    {
        return $this->hasMany(OutboundClick::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** 公開中のみ（ADM-005 / PUB-002） */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    /**
     * 期限切れを除外（PUB-009）。
     * 申込締切または開催終了日が過去のものは一覧から除外する。
     */
    public function scopeNotExpired(Builder $query): Builder
    {
        $today = now()->startOfDay();

        return $query->where(function (Builder $q) use ($today) {
            $q->whereNull('application_deadline')->orWhere('application_deadline', '>=', $today);
        })->where(function (Builder $q) use ($today) {
            $q->whereNull('end_at')->orWhere('end_at', '>=', $today)->orWhere('is_recurring', true);
        });
    }

    /** 期限切れ判定（PUB-009 / 表示用） */
    public function isExpired(): bool
    {
        $today = now()->startOfDay();

        if ($this->is_recurring) {
            return false;
        }
        if ($this->application_deadline && $this->application_deadline->lt($today)) {
            return true;
        }
        if ($this->end_at && $this->end_at->lt($today)) {
            return true;
        }

        return false;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft' => '下書き',
            'review' => '確認待ち',
            'published' => '公開中',
            'archived' => 'アーカイブ',
            'rejected' => '却下',
            default => $this->status,
        };
    }
}
