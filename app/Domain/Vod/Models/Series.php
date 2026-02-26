<?php

namespace App\Domain\Vod\Models;

use App\Domain\Stream\Models\StreamCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Series extends Model
{
    protected $table = 'series';
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'admin_enabled' => 'boolean',
        ];
    }

    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class)->orderBy('season_number')->orderBy('episode_number');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(StreamCategory::class, 'category_id');
    }

    public function getEpisodeCountAttribute(): int
    {
        return $this->episodes()->count();
    }

    public function getSeasonCountAttribute(): int
    {
        return $this->episodes()->distinct('season_number')->count('season_number');
    }
}
