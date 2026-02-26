<?php

namespace App\Domain\Security\Models;

use Illuminate\Database\Eloquent\Model;

class TranscodeProfile extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['options' => 'array'];
    }
}
