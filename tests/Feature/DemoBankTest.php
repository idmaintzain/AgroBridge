<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\LedgerEntry;
use App\Models\Listing;
use App\Models\User;
use App\Services\Escrow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoBankTest extends TestCase
{
    use RefreshDatabase;

    public function test_fund_increases_available_and_writes_a_credit(): void
    {
        $buyer = User::factory()->create(['buys' => true, 'sells' => false]);

        $this->actingAs($buyer)
            ->post(route('wallet.fund'), ['amount' => '2500.00'])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertSame(250_000, $buyer->wallet->fresh()->available_kobo);
        $this->assertDatabaseHas('ledger_entries', [
            'user_id' => $buyer->id,
            'account' => 'wallet',
            'direction' => 'credit',
            'type' => 'topup',
            'amount_kobo' => 250_000,
        ]);
    }

    public function test_withdraw_and_transfer_refuse_amounts_above_available(): void
    {
        $sender = User::factory()->create(['buys' => true, 'sells' => false]);
        $recipient = User::factory()->create(['buys' => true, 'sells' => true, 'email' => 'farmer@example.test']);
        $sender->wallet->update(['available_kobo' => 10_000]);

        $this->actingAs($sender)
            ->post(route('wallet.withdraw'), ['amount' => '200.00'])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->actingAs($sender)
            ->post(route('wallet.transfer'), ['email' => $recipient->email, 'amount' => '200.00'])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(10_000, $sender->wallet->fresh()->available_kobo);
        $this->assertSame(0, $recipient->wallet->fresh()->available_kobo);
        $this->assertSame(0, LedgerEntry::query()->where('user_id', $sender->id)->count());
    }

    public function test_transfer_moves_kobo_and_writes_both_ledger_rows(): void
    {
        $sender = User::factory()->create(['name' => 'Chinedu Okonkwo', 'buys' => true, 'sells' => false]);
        $recipient = User::factory()->create(['name' => 'Iya Musa Adeyemi', 'buys' => false, 'sells' => true, 'email' => 'farmer@example.test']);
        $sender->wallet->update(['available_kobo' => 500_000]);

        $this->actingAs($sender)
            ->post(route('wallet.transfer'), ['email' => $recipient->email, 'amount' => '150.00'])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertSame(350_000, $sender->wallet->fresh()->available_kobo);
        $this->assertSame(150_000, $recipient->wallet->fresh()->available_kobo);
        $this->assertDatabaseHas('ledger_entries', [
            'user_id' => $sender->id,
            'type' => 'transfer',
            'direction' => 'debit',
            'amount_kobo' => 150_000,
            'memo' => 'Sent to Iya Musa Adeyemi',
        ]);
        $this->assertDatabaseHas('ledger_entries', [
            'user_id' => $recipient->id,
            'type' => 'transfer',
            'direction' => 'credit',
            'amount_kobo' => 150_000,
            'memo' => 'Received from Chinedu Okonkwo',
        ]);
    }

    public function test_cannot_transfer_to_self_or_to_an_admin(): void
    {
        $buyer = User::factory()->create(['buys' => true, 'sells' => false]);
        $admin = User::factory()->create(['is_admin' => true, 'buys' => false, 'sells' => false]);
        $buyer->wallet->update(['available_kobo' => 1_000_000]);

        $this->actingAs($buyer)
            ->post(route('wallet.transfer'), ['email' => $buyer->email, 'amount' => '10.00'])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->actingAs($buyer)
            ->post(route('wallet.transfer'), ['email' => $admin->email, 'amount' => '10.00'])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(1_000_000, $buyer->wallet->fresh()->available_kobo);
        $this->assertSame(0, $admin->wallet->fresh()->available_kobo);
    }

    public function test_funded_buyer_can_pay_and_escrow_still_refuses_overspend(): void
    {
        $farmer = User::factory()->create(['sells' => true, 'buys' => false]);
        $buyer = User::factory()->create(['sells' => false, 'buys' => true]);

        $listing = Listing::query()->create([
            'user_id' => $farmer->id,
            'crop' => 'Maize',
            'unit' => 'bag',
            'quantity_on_hand' => 80,
            'quantity_reserved' => 0,
            'price_per_unit_kobo' => 1_000_000,
            'state' => 'Oyo',
            'lga' => 'Iddo',
            'collect_by' => now()->addWeek()->toDateString(),
            'is_active' => true,
        ]);

        $this->actingAs($buyer)
            ->post(route('wallet.fund'), ['amount' => '10200.00'])
            ->assertRedirect()
            ->assertSessionHas('status');

        $order = app(Escrow::class)->place($listing, $buyer, 1);
        $this->actingAs($buyer)->post(route('orders.pay', $order))->assertRedirect()->assertSessionHas('status');
        $this->assertSame(OrderStatus::Paid, $order->fresh()->status);
        $this->assertSame(0, $buyer->wallet->fresh()->available_kobo);

        $second = app(Escrow::class)->place($listing->fresh(), $buyer, 1);
        $this->actingAs($buyer)
            ->post(route('orders.pay', $second))
            ->assertRedirect()
            ->assertSessionHas('error');
        $this->assertSame(OrderStatus::AwaitingPayment, $second->fresh()->status);
        $this->assertSame(0, $buyer->wallet->fresh()->available_kobo);
    }

    public function test_admin_cannot_use_the_demo_bank(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'buys' => false, 'sells' => false]);

        $this->actingAs($admin)->post(route('wallet.fund'), ['amount' => '10.00'])->assertForbidden();
        $this->actingAs($admin)->get(route('office.wallet'))->assertForbidden();
    }
}
