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

    /** 掲載種別：活動・イベント / 制度・支援 / 相談・紹介 */
    public const KINDS = ['event', 'program', 'intro'];

    protected $fillable = [
        'municipality_id', 'category_id', 'kind', 'title', 'slug', 'summary', 'description',
        'quote_text', 'quote_source', 'visibility',
        'source_url', 'attribution_name', 'cited_at', 'apply_url', 'image_url', 'organizer_name',
        'application_deadline', 'start_at', 'end_at', 'is_recurring',
        'fee_text', 'child_friendly', 'beginner_friendly', 'online_available',
        'has_reward', 'transport_support', 'lodging_support', 'target_audience', 'capacity',
        'status', 'verified_at', 'extraction_confidence', 'meta_title', 'meta_description',
    ];

    protected $casts = [
        'application_deadline' => 'date',
        'cited_at' => 'date',
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

    /** 公開ステータス（ADM-005）。社内のみ(internal)も含む。管理・集計用。 */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    /** 公開メディアに掲載される活動（公開ステータス かつ visibility=public）。利用者向け・KPI用。 */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('status', 'published')->where('visibility', 'public');
    }

    /**
     * 期限切れを除外（PUB-009）。
     * 制度・相談（program/intro）は常設のため対象外。活動（event）のみ締切・開催日で判定。
     */
    public function scopeNotExpired(Builder $query): Builder
    {
        $today = now()->startOfDay();

        return $query->where(function (Builder $outer) use ($today) {
            $outer->where('kind', '!=', 'event') // 制度・相談は常時有効
                ->orWhere(function (Builder $q) use ($today) {
                    $q->where(function (Builder $d) use ($today) {
                        $d->whereNull('application_deadline')->orWhere('application_deadline', '>=', $today);
                    })->where(function (Builder $e) use ($today) {
                        $e->whereNull('end_at')->orWhere('end_at', '>=', $today)->orWhere('is_recurring', true);
                    });
                });
        });
    }

    /** 期限切れ判定（PUB-009 / 表示用）。制度・相談は期限切れにならない。 */
    public function isExpired(): bool
    {
        $today = now()->startOfDay();

        if ($this->kind !== 'event' || $this->is_recurring) {
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

    public function kindLabel(): string
    {
        return match ($this->kind) {
            'program' => '制度・支援',
            'intro' => '相談・紹介',
            default => '活動・イベント',
        };
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
