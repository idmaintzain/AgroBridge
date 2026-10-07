<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

class Wallet extends Model
{
    protected $fillable = [
        'user_id',
        'available_kobo',
        'payable_kobo',
    ];

    protected function casts(): array
    {
        return [
            'available_kobo' => 'integer',
            'payable_kobo' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Wallet $wallet): void {
            if ($wallet->available_kobo < 0 || $wallet->payable_kobo < 0) {
                throw new RuntimeException('A wallet balance cannot fall below zero.');
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
