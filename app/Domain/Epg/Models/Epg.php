<?php

namespace App\Domain\Epg\Models;

use Illuminate\Database\Eloquent\Model;

class Epg extends Model
{
    protected $table = 'epg';
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'last_updated_at' => 'datetime',
        ];
    }
}
