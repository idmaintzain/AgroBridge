<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformSettingRevision extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'key',
        'old_value',
        'new_value',
        'reason',
        'user_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }
}
