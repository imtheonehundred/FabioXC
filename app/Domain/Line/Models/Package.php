<?php

namespace App\Domain\Line\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Package extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'bouquet_channels' => 'array',
            'bouquet_movies' => 'array',
            'bouquet_series' => 'array',
            'is_trial' => 'boolean',
            'is_official' => 'boolean',
            'is_addon' => 'boolean',
        ];
    }

    public function lines(): BelongsToMany
    {
        return $this->belongsToMany(Line::class, 'line_package');
    }
}
