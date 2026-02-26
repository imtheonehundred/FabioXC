<?php

namespace App\Domain\Stream\Models;

use App\Domain\Server\Models\Server;
use App\Domain\Epg\Models\Epg;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stream extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'added' => 'datetime',
            'admin_enabled' => 'boolean',
            'tv_archive' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(StreamCategory::class, 'category_id');
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function epg(): BelongsTo
    {
        return $this->belongsTo(Epg::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            0 => 'Offline',
            1 => 'Online',
            2 => 'Starting',
            3 => 'Error',
            4 => 'Stopping',
            default => 'Unknown',
        };
    }
}
