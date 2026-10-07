<?php

namespace App\Services;

use App\Models\LedgerEntry;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class DemoBank
{
    public const MAX_TOPUP_KOBO = 100_000_000;

    public function fund(User $user, int $kobo): void
    {
        $this->assertPositive($kobo);

        if ($kobo > self::MAX_TOPUP_KOBO) {
            throw new WalletException('A demonstration top-up cannot exceed ₦1,000,000.00.');
        }

        DB::transaction(function () use ($user, $kobo): void {
            $this->assertNotAdmin($user);
            $wallet = $this->lockWallet($user);
            $wallet->available_kobo += $kobo;
            $wallet->save();

            $this->post($user, 'credit', 'topup', $kobo, 'Demonstration top-up');
        });
    }

    public function withdraw(User $user, int $kobo): void
    {
        $this->assertPositive($kobo);

        DB::transaction(function () use ($user, $kobo): void {
            $this->assertNotAdmin($user);
            $wallet = $this->lockWallet($user);
            $this->assertEnough($wallet, $kobo);
            $wallet->available_kobo -= $kobo;
            $wallet->save();

            $this->post($user, 'debit', 'withdraw', $kobo, 'Demonstration withdrawal');
        });
    }

    public function transfer(User $sender, string $recipientEmail, int $kobo): void
    {
        $this->assertPositive($kobo);

        DB::transaction(function () use ($sender, $recipientEmail, $kobo): void {
            $this->assertNotAdmin($sender);

            $recipient = User::query()->where('email', $recipientEmail)->lockForUpdate()->first();

            if ($recipient === null) {
                throw new WalletException('No AgroBridge account uses that email.');
            }

            if ($recipient->id === $sender->id) {
                throw new WalletException('You cannot transfer to your own wallet.');
            }

            $this->assertNotAdmin($recipient);

            $from = $this->lockWallet($sender);
            $to = $this->lockWallet($recipient);
            $this->assertEnough($from, $kobo);

            $from->available_kobo -= $kobo;
            $from->save();
            $to->available_kobo += $kobo;
            $to->save();

            $this->post($sender, 'debit', 'transfer', $kobo, 'Sent to '.$recipient->name);
            $this->post($recipient, 'credit', 'transfer', $kobo, 'Received from '.$sender->name);
        });
    }

    private function lockWallet(User $user): Wallet
    {
        $wallet = Wallet::query()->where('user_id', $user->id)->lockForUpdate()->first();

        if ($wallet === null) {
            throw new WalletException('This account has no wallet.');
        }

        return $wallet;
    }

    private function assertNotAdmin(User $user): void
    {
        if ($user->is_admin) {
            throw new WalletException('Administrator accounts do not hold demonstration money.');
        }
    }

    private function assertPositive(int $kobo): void
    {
        if ($kobo < 1) {
            throw new WalletException('Enter an amount of at least ₦0.01.');
        }
    }

    private function assertEnough(Wallet $wallet, int $kobo): void
    {
        if ($wallet->available_kobo < $kobo) {
            throw new WalletException('Available funds are not enough. Owed payouts cannot be withdrawn or sent until they land in Available.');
        }
    }

    private function post(User $user, string $direction, string $type, int $amount, string $memo): void
    {
        LedgerEntry::query()->create([
            'order_id' => null,
            'user_id' => $user->id,
            'account' => 'wallet',
            'direction' => $direction,
            'type' => $type,
            'amount_kobo' => $amount,
            'memo' => $memo,
            'created_at' => now(),
        ]);
    }
}
