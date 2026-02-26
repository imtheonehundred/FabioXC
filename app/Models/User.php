<?php

namespace App\Models;

use App\Domain\User\Models\MemberGroup;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    protected $guarded = ['id'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'admin_permissions' => 'array',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(MemberGroup::class, 'member_group_id');
    }
}
