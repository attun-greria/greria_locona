<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'type'];

    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(Activity::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
