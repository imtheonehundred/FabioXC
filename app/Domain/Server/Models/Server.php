<?php

namespace App\Domain\Server\Models;

use App\Domain\Stream\Models\Stream;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Server extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_main' => 'boolean',
            'last_check_at' => 'datetime',
        ];
    }

    public function streams(): HasMany
    {
        return $this->hasMany(Stream::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            0 => 'Offline',
            1 => 'Online',
            2 => 'Maintenance',
            default => 'Unknown',
        };
    }
}
