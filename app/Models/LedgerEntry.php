<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

class LedgerEntry extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'user_id',
        'account',
        'direction',
        'type',
        'amount_kobo',
        'memo',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (): void {
            throw new RuntimeException('Ledger entries are append-only.');
        });

        static::deleting(function (): void {
            throw new RuntimeException('Ledger entries are append-only.');
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
