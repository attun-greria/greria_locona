<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActivityCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'icon', 'display_order', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class, 'category_id');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
