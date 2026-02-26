<?php

namespace App\Domain\Device\Models;

use App\Domain\Line\Models\Line;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Device extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'admin_enabled' => 'boolean',
            'last_seen_at' => 'datetime',
        ];
    }

    public function line(): BelongsTo
    {
        return $this->belongsTo(Line::class);
    }
}
