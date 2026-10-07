<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\LedgerEntry;
use App\Models\Listing;
use App\Models\Order;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Escrow
{
    private const ALPHABET = 'ABCDEFGHJKLMNPQRTUVWXYZ2346789';

    public function __construct(private Settings $settings) {}

    public function place(Listing $listing, User $buyer, int $quantity): Order
    {
        return DB::transaction(function () use ($listing, $buyer, $quantity): Order {
            $listing = Listing::query()->whereKey($listing->id)->lockForUpdate()->firstOrFail();

            if ($buyer->id === $listing->user_id) {
                throw new EscrowException('A farmer cannot buy their own listing.');
            }

            if (! $buyer->buys) {
                throw new EscrowException('This account is not set up to buy produce.');
            }

            if (! $listing->is_active) {
                throw new EscrowException('This listing is no longer on the market.');
            }

            if ($quantity < 1 || $quantity > $listing->availableQuantity()) {
                throw new EscrowException('That quantity is not available.');
            }

            $itemKobo = $listing->price_per_unit_kobo * $quantity;
            $quote = $this->settings->quote($itemKobo);

            $listing->quantity_reserved += $quantity;
            $listing->save();

            return Order::query()->create([
                'listing_id' => $listing->id,
                'buyer_id' => $buyer->id,
                'farmer_id' => $listing->user_id,
                'quantity' => $quantity,
                'unit_price_kobo' => $listing->price_per_unit_kobo,
                'item_kobo' => $itemKobo,
                'buyer_fee_kobo' => $quote['buyer_fee_kobo'],
                'seller_fee_kobo' => $quote['seller_fee_kobo'],
                'total_kobo' => $quote['total_kobo'],
                'payout_kobo' => $quote['payout_kobo'],
                'platform_fee_kobo' => $quote['platform_fee_kobo'],
                'buyer_fee_percent' => $quote['buyer_fee_percent'],
                'seller_fee_percent' => $quote['seller_fee_percent'],
                'buyer_fee_floor_kobo' => $quote['buyer_fee_floor_kobo'],
                'seller_fee_floor_kobo' => $quote['seller_fee_floor_kobo'],
                'dispute_window_hours' => $quote['dispute_window_hours'],
                'collection_window_hours' => $quote['collection_window_hours'],
                'status' => OrderStatus::AwaitingPayment,
                'payment_deadline_at' => now()->addHours($quote['payment_hours']),
            ]);
        });
    }

    public function pay(Order $order, User $buyer): void
    {
        DB::transaction(function () use ($order, $buyer): void {
            $order = $this->lockOrder($order);
            $this->assertBuyer($order, $buyer);
            $this->assertStatus($order, OrderStatus::AwaitingPayment);

            $wallet = $this->lockWallet($buyer);

            if ($wallet->available_kobo < $order->total_kobo) {
                throw new EscrowException('Your wallet does not have enough for this order. Nothing was held.');
            }

            $wallet->available_kobo -= $order->total_kobo;
            $wallet->save();

            $this->post($order, $buyer, 'buyer_wallet', 'debit', 'hold', $order->total_kobo, 'Buyer payment held');
            $this->post($order, null, 'escrow', 'credit', 'hold', $order->total_kobo, 'Held by AgroBridge');

            $listing = Listing::query()->whereKey($order->listing_id)->lockForUpdate()->firstOrFail();
            $listing->quantity_on_hand -= $order->quantity;
            $listing->quantity_reserved -= $order->quantity;
            $listing->save();

            $order->status = OrderStatus::Paid;
            $order->paid_at = now();
            $order->collection_deadline_at = now()->addHours($order->collection_window_hours);
            $order->collection_code = Crypt::encryptString($this->makeCode());
            $order->save();
        });
    }

    public function confirm(Order $order, User $buyer): void
    {
        DB::transaction(function () use ($order, $buyer): void {
            $order = $this->lockOrder($order);
            $this->assertBuyer($order, $buyer);
            $this->recordHandover($order);
        });
    }

    public function submitCode(Order $order, User $farmer, string $code): void
    {
        $failedAttempts = DB::transaction(function () use ($order, $farmer, $code): ?int {
            $order = $this->lockOrder($order);

            if ($farmer->id !== $order->farmer_id) {
                throw new EscrowException('Only the farmer on this order can enter the collection code.');
            }

            $this->assertStatus($order, OrderStatus::Paid);

            if ($order->code_attempts >= 5) {
                throw new EscrowException('The collection code is locked after five wrong attempts. The buyer can still confirm receipt.');
            }

            $plain = $order->plainCode();
            $given = strtoupper(preg_replace('/\s+/', '', $code) ?? '');

            if ($plain === null || ! hash_equals($plain, $given)) {
                $order->code_attempts++;
                $order->save();

                return $order->code_attempts;
            }

            $this->recordHandover($order);

            return null;
        });

        if ($failedAttempts !== null) {
            $left = 5 - $failedAttempts;

            throw new EscrowException($left > 0
                ? "That code does not match. {$left} attempts left."
                : 'That code does not match. The collection code is now locked.');
        }
    }

    public function dispute(Order $order, User $buyer, string $reason): void
    {
        DB::transaction(function () use ($order, $buyer, $reason): void {
            $order = $this->lockOrder($order);
            $this->assertBuyer($order, $buyer);
            $this->assertStatus($order, OrderStatus::Delivered);

            $order->status = OrderStatus::Disputed;
            $order->dispute_reason = $reason;
            $order->dispute_opened_at = now();
            $order->save();
        });
    }

    public function cancel(Order $order, User $buyer): void
    {
        DB::transaction(function () use ($order, $buyer): void {
            $order = $this->lockOrder($order);
            $this->assertBuyer($order, $buyer);
            $this->assertStatus($order, OrderStatus::AwaitingPayment);
            $this->releaseReservation($order);
            $order->status = OrderStatus::Cancelled;
            $order->cancelled_at = now();
            $order->save();
        });
    }

    public function resolve(Order $order, User $admin, string $outcome, string $note): void
    {
        if (! $admin->is_admin) {
            throw new EscrowException('Only an administrator can resolve a dispute.');
        }

        DB::transaction(function () use ($order, $outcome, $note): void {
            $order = $this->lockOrder($order);
            $this->assertStatus($order, OrderStatus::Disputed);
            $order->resolution_note = $note;

            if ($outcome === 'release') {
                $this->release($order);
                $this->payout($order);
            } elseif ($outcome === 'refund') {
                $this->refund($order);
            } else {
                throw new EscrowException('Choose release or refund.');
            }
        });
    }

    public function settle(): int
    {
        $changed = 0;

        $awaiting = Order::query()
            ->where('status', OrderStatus::AwaitingPayment)
            ->where('payment_deadline_at', '<=', now())
            ->pluck('id');

        foreach ($awaiting as $id) {
            $changed += $this->safely($id, function (Order $order): void {
                $this->assertStatus($order, OrderStatus::AwaitingPayment);
                $this->releaseReservation($order);
                $order->status = OrderStatus::Cancelled;
                $order->cancelled_at = now();
                $order->save();
            });
        }

        $uncollected = Order::query()
            ->where('status', OrderStatus::Paid)
            ->where('collection_deadline_at', '<=', now())
            ->pluck('id');

        foreach ($uncollected as $id) {
            $changed += $this->safely($id, function (Order $order): void {
                $this->assertStatus($order, OrderStatus::Paid);
                $this->refund($order);
            });
        }

        $ready = Order::query()
            ->where('status', OrderStatus::Delivered)
            ->where('dispute_window_ends_at', '<=', now())
            ->pluck('id');

        foreach ($ready as $id) {
            $changed += $this->safely($id, function (Order $order): void {
                $this->assertStatus($order, OrderStatus::Delivered);
                $this->release($order);
                $this->payout($order);
            });
        }

        $released = Order::query()->where('status', OrderStatus::Released)->pluck('id');

        foreach ($released as $id) {
            $changed += $this->safely($id, function (Order $order): void {
                $this->assertStatus($order, OrderStatus::Released);
                $this->payout($order);
            });
        }

        return $changed;
    }

    private function recordHandover(Order $order): void
    {
        $this->assertStatus($order, OrderStatus::Paid);
        $order->status = OrderStatus::Delivered;
        $order->delivered_at = now();
        $order->dispute_window_ends_at = now()->addHours($order->dispute_window_hours);
        $order->save();
    }

    private function release(Order $order): void
    {
        $farmer = User::query()->findOrFail($order->farmer_id);
        $wallet = $this->lockWallet($farmer);
        $wallet->payable_kobo += $order->payout_kobo;
        $wallet->save();

        $this->post($order, null, 'escrow', 'debit', 'release', $order->total_kobo, 'Escrow released');
        $this->post($order, $farmer, 'farmer_payable', 'credit', 'release', $order->payout_kobo, 'Owed to farmer');

        if ($order->platform_fee_kobo > 0) {
            $this->post($order, null, 'platform_fee', 'credit', 'fee', $order->platform_fee_kobo, 'Platform fee');
        }

        $order->status = OrderStatus::Released;
        $order->released_at = now();
        $order->save();
    }

    private function payout(Order $order): void
    {
        $farmer = User::query()->findOrFail($order->farmer_id);
        $wallet = $this->lockWallet($farmer);

        if ($wallet->payable_kobo < $order->payout_kobo) {
            throw new EscrowException('The farmer payable balance is short of this payout.');
        }

        $wallet->payable_kobo -= $order->payout_kobo;
        $wallet->available_kobo += $order->payout_kobo;
        $wallet->save();

        $this->post($order, $farmer, 'farmer_payable', 'debit', 'payout', $order->payout_kobo, 'Payout leaves payable');
        $this->post($order, $farmer, 'farmer_wallet', 'credit', 'payout', $order->payout_kobo, 'Paid to farmer wallet');

        $order->status = OrderStatus::Completed;
        $order->completed_at = now();
        $order->save();
    }

    private function refund(Order $order): void
    {
        $buyer = User::query()->findOrFail($order->buyer_id);
        $wallet = $this->lockWallet($buyer);
        $wallet->available_kobo += $order->total_kobo;
        $wallet->save();

        $this->post($order, null, 'escrow', 'debit', 'refund', $order->total_kobo, 'Escrow returned');
        $this->post($order, $buyer, 'buyer_wallet', 'credit', 'refund', $order->total_kobo, 'Buyer refunded, fee included');

        $listing = Listing::query()->whereKey($order->listing_id)->lockForUpdate()->firstOrFail();
        $listing->quantity_on_hand += $order->quantity;
        $listing->save();

        $order->status = OrderStatus::Refunded;
        $order->refunded_at = now();
        $order->save();
    }

    private function releaseReservation(Order $order): void
    {
        $listing = Listing::query()->whereKey($order->listing_id)->lockForUpdate()->firstOrFail();
        $listing->quantity_reserved -= $order->quantity;

        if ($listing->quantity_reserved < 0) {
            throw new EscrowException('Reserved quantity cannot fall below zero.');
        }

        $listing->save();
    }

    private function lockOrder(Order $order): Order
    {
        return Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
    }

    private function lockWallet(User $user): Wallet
    {
        $wallet = Wallet::query()->where('user_id', $user->id)->lockForUpdate()->first();

        if ($wallet === null) {
            throw new EscrowException('This account has no wallet.');
        }

        return $wallet;
    }

    private function assertBuyer(Order $order, User $buyer): void
    {
        if ($buyer->id !== $order->buyer_id) {
            throw new EscrowException('Only the buyer can do that.');
        }
    }

    private function assertStatus(Order $order, OrderStatus $status): void
    {
        if ($order->status !== $status) {
            throw new EscrowException('This order is '.$order->status->label().', so that step is closed.');
        }
    }

    private function post(?Order $order, ?User $user, string $account, string $direction, string $type, int $amount, string $memo): void
    {
        if ($amount < 1) {
            return;
        }

        LedgerEntry::query()->create([
            'order_id' => $order?->id,
            'user_id' => $user?->id,
            'account' => $account,
            'direction' => $direction,
            'type' => $type,
            'amount_kobo' => $amount,
            'memo' => $memo,
            'created_at' => now(),
        ]);
    }

    private function makeCode(): string
    {
        $code = '';
        $max = strlen(self::ALPHABET) - 1;

        for ($i = 0; $i < 6; $i++) {
            $code .= self::ALPHABET[random_int(0, $max)];
        }

        return $code;
    }

    private function safely(int $orderId, callable $callback): int
    {
        try {
            DB::transaction(function () use ($orderId, $callback): void {
                $order = Order::query()->whereKey($orderId)->lockForUpdate()->firstOrFail();
                $callback($order);
            });

            return 1;
        } catch (\Throwable $e) {
            Log::warning('Order settlement skipped', [
                'order_id' => $orderId,
                'message' => $e->getMessage(),
            ]);

            return 0;
        }
    }
}
