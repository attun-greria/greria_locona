<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Municipality extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'catchphrase', 'prefecture', 'city', 'summary',
        'official_url', 'image_url', 'related_urls', 'line_url',
        'contact_name', 'contact_url', 'is_published',
    ];

    protected $casts = [
        'related_urls' => 'array',
        'is_published' => 'boolean',
    ];

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function publishedActivities(): HasMany
    {
        return $this->activities()->where('status', 'published')->where('visibility', 'public');
    }

    public function sources(): HasMany
    {
        return $this->hasMany(Source::class);
    }

    public function lineChannels(): HasMany
    {
        return $this->hasMany(LineChannel::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
