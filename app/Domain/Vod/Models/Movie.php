<?php

namespace App\Domain\Vod\Models;

use App\Domain\Stream\Models\StreamCategory;
use App\Domain\Server\Models\Server;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Movie extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'added' => 'datetime',
            'admin_enabled' => 'boolean',
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
}
