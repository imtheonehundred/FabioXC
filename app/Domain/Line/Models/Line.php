<?php

namespace App\Domain\Line\Models;

use App\Domain\User\Models\MemberGroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Line extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'exp_date' => 'datetime',
            'added' => 'datetime',
            'bouquet' => 'array',
            'is_trial' => 'boolean',
            'admin_enabled' => 'boolean',
            'is_restreamer' => 'boolean',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(MemberGroup::class, 'member_group_id');
    }

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class, 'line_package');
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->exp_date && $this->exp_date->isPast();
    }

    public function getStatusLabelAttribute(): string
    {
        if (!$this->admin_enabled) return 'Disabled';
        if ($this->is_expired) return 'Expired';
        if ($this->active_connections > 0) return 'Online';
        return 'Offline';
    }
}
