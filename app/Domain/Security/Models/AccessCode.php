<?php

namespace App\Domain\Security\Models;

use Illuminate\Database\Eloquent\Model;

class AccessCode extends Model
{
    protected $guarded = ['id'];

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            0 => 'Admin', 1 => 'Reseller', 2 => 'Ministra',
            3 => 'Admin API', 4 => 'Reseller API', 5 => 'Ministra New', 6 => 'Player',
            default => 'Unknown',
        };
    }
}
