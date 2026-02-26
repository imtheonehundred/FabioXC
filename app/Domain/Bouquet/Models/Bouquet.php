<?php

namespace App\Domain\Bouquet\Models;

use Illuminate\Database\Eloquent\Model;

class Bouquet extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'bouquet_channels' => 'array',
            'bouquet_movies' => 'array',
            'bouquet_series' => 'array',
            'bouquet_radios' => 'array',
        ];
    }
}
