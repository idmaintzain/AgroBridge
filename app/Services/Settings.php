<?php

namespace App\Services;

use App\Models\PlatformSetting;
use App\Models\PlatformSettingRevision;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class Settings
{
    public function int(string $key): int
    {
        $value = PlatformSetting::query()->where('key', $key)->value('value');

        if ($value === null) {
            throw new EscrowException("Missing platform setting [{$key}].");
        }

        return (int) $value;
    }

    /**
     * @return array{buyer_fee_kobo: int, seller_fee_kobo: int, total_kobo: int, payout_kobo: int, platform_fee_kobo: int, buyer_fee_percent: int, seller_fee_percent: int, buyer_fee_floor_kobo: int, seller_fee_floor_kobo: int, dispute_window_hours: int, payment_hours: int, collection_window_hours: int}
     */
    public function quote(int $itemKobo): array
    {
        $buyerPercent = $this->int('fees.buyer_percent');
        $buyerFloor = $this->int('fees.buyer_floor_kobo');
        $sellerPercent = $this->int('fees.seller_percent');
        $sellerFloor = $this->int('fees.seller_floor_kobo');

        $buyerFee = max((int) round($itemKobo * $buyerPercent / 100), $buyerFloor);
        $sellerFee = min($itemKobo, max((int) round($itemKobo * $sellerPercent / 100), $sellerFloor));

        return [
            'buyer_fee_kobo' => $buyerFee,
            'seller_fee_kobo' => $sellerFee,
            'total_kobo' => $itemKobo + $buyerFee,
            'payout_kobo' => $itemKobo - $sellerFee,
            'platform_fee_kobo' => $buyerFee + $sellerFee,
            'buyer_fee_percent' => $buyerPercent,
            'seller_fee_percent' => $sellerPercent,
            'buyer_fee_floor_kobo' => $buyerFloor,
            'seller_fee_floor_kobo' => $sellerFloor,
            'dispute_window_hours' => $this->int('windows.dispute_hours'),
            'payment_hours' => $this->int('windows.payment_hours'),
            'collection_window_hours' => $this->int('windows.collection_hours'),
        ];
    }

    public function set(string $key, string $value, User $admin, string $reason): void
    {
        DB::transaction(function () use ($key, $value, $admin, $reason): void {
            $setting = PlatformSetting::query()->where('key', $key)->lockForUpdate()->first();

            if ($setting === null) {
                throw new EscrowException("Unknown setting [{$key}].");
            }

            if ($setting->value === $value) {
                return;
            }

            PlatformSettingRevision::query()->create([
                'key' => $key,
                'old_value' => $setting->value,
                'new_value' => $value,
                'reason' => $reason,
                'user_id' => $admin->id,
                'created_at' => now(),
            ]);

            $setting->value = $value;
            $setting->save();
        });
    }
}
