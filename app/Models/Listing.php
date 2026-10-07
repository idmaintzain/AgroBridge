<?php

namespace App\Models;

use App\Enums\ProduceUnit;
use App\Support\ProducePhotos;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use RuntimeException;

class Listing extends Model
{
    protected static function booted(): void
    {
        static::saving(function (Listing $listing): void {
            if ($listing->quantity_on_hand < 0 || $listing->quantity_reserved < 0 || $listing->quantity_reserved > $listing->quantity_on_hand) {
                throw new RuntimeException('Listing quantities are inconsistent.');
            }
        });
    }

    protected $fillable = [
        'user_id',
        'crop',
        'unit',
        'quantity_on_hand',
        'quantity_reserved',
        'price_per_unit_kobo',
        'state',
        'lga',
        'collect_by',
        'description',
        'image_path',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'unit' => ProduceUnit::class,
            'collect_by' => 'date',
            'is_active' => 'boolean',
            'quantity_on_hand' => 'integer',
            'quantity_reserved' => 'integer',
            'price_per_unit_kobo' => 'integer',
        ];
    }

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function availableQuantity(): int
    {
        return $this->quantity_on_hand - $this->quantity_reserved;
    }

    public function unitWord(int $quantity): string
    {
        $unit = $this->unit instanceof ProduceUnit ? $this->unit->value : (string) $this->unit;

        if ($unit === 'kg' || $quantity === 1) {
            return $unit;
        }

        return $unit.'s';
    }

    public function photoUrl(): string
    {
        if ($this->image_path) {
            return asset('storage/'.$this->image_path);
        }

        return ProducePhotos::url($this->crop);
    }
}
