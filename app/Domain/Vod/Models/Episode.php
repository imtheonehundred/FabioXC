<?php

namespace App\Domain\Vod\Models;

use App\Domain\Server\Models\Server;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Episode extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'added' => 'datetime',
            'admin_enabled' => 'boolean',
        ];
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class);
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }
}
