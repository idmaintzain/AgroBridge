<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class Order extends Model
{
    protected $hidden = [
        'collection_code',
    ];

    protected $fillable = [
        'listing_id',
        'buyer_id',
        'farmer_id',
        'quantity',
        'unit_price_kobo',
        'item_kobo',
        'buyer_fee_kobo',
        'seller_fee_kobo',
        'total_kobo',
        'payout_kobo',
        'platform_fee_kobo',
        'buyer_fee_percent',
        'seller_fee_percent',
        'buyer_fee_floor_kobo',
        'seller_fee_floor_kobo',
        'dispute_window_hours',
        'collection_window_hours',
        'status',
        'collection_code',
        'code_attempts',
        'payment_deadline_at',
        'collection_deadline_at',
        'dispute_window_ends_at',
        'paid_at',
        'delivered_at',
        'released_at',
        'completed_at',
        'cancelled_at',
        'refunded_at',
        'dispute_opened_at',
        'dispute_reason',
        'resolution_note',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'quantity' => 'integer',
            'unit_price_kobo' => 'integer',
            'item_kobo' => 'integer',
            'buyer_fee_kobo' => 'integer',
            'seller_fee_kobo' => 'integer',
            'total_kobo' => 'integer',
            'payout_kobo' => 'integer',
            'platform_fee_kobo' => 'integer',
            'code_attempts' => 'integer',
            'payment_deadline_at' => 'datetime',
            'collection_deadline_at' => 'datetime',
            'dispute_window_ends_at' => 'datetime',
            'paid_at' => 'datetime',
            'delivered_at' => 'datetime',
            'released_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'refunded_at' => 'datetime',
            'dispute_opened_at' => 'datetime',
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'farmer_id');
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class);
    }

    public function plainCode(): ?string
    {
        if ($this->collection_code === null) {
            return null;
        }

        return Crypt::decryptString($this->collection_code);
    }

    public function involves(User $user): bool
    {
        return $user->is_admin || $user->id === $this->buyer_id || $user->id === $this->farmer_id;
    }
}
