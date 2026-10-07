<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Listing;
use App\Models\User;
use App\Services\Escrow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class EscrowTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_farmer_cannot_release_money_by_claiming_delivery(): void
    {
        [$farmer, $buyer, $listing] = $this->market();
        $order = app(Escrow::class)->place($listing, $buyer, 2);
        app(Escrow::class)->pay($order, $buyer->fresh());
        $code = $order->fresh()->plainCode();

        $this->actingAs($farmer)->post(route('orders.confirm', $order))->assertForbidden();
        $this->actingAs($farmer)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertDontSee($code);

        $this->artisan('orders:settle')->assertSuccessful();

        $this->assertSame(OrderStatus::Paid, $order->fresh()->status);
        $this->assertSame(0, $farmer->wallet->fresh()->available_kobo);
        $this->assertSame(0, $farmer->wallet->fresh()->payable_kobo);
    }

    public function test_buyer_cannot_overspend_the_wallet(): void
    {
        [, $buyer, $listing] = $this->market();
        $buyer->wallet->update(['available_kobo' => 100]);
        $order = app(Escrow::class)->place($listing, $buyer, 1);

        $this->actingAs($buyer)
            ->post(route('orders.pay', $order))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(100, $buyer->wallet->fresh()->available_kobo);
        $this->assertSame(OrderStatus::AwaitingPayment, $order->fresh()->status);
        $this->assertSame(80, $listing->fresh()->quantity_on_hand);
        $this->assertSame(1, $listing->fresh()->quantity_reserved);
    }

    public function test_unpaid_orders_return_reserved_stock(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-27 09:00:00'));
        [, $buyer, $listing] = $this->market();
        $order = app(Escrow::class)->place($listing, $buyer, 10);

        $this->assertSame(10, $listing->fresh()->quantity_reserved);
        $this->assertSame(70, $listing->fresh()->availableQuantity());

        Carbon::setTestNow(Carbon::parse('2026-09-28 10:00:00'));
        $this->artisan('orders:settle')->assertSuccessful();

        $listing->refresh();
        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);
        $this->assertSame(0, $listing->quantity_reserved);
        $this->assertSame(80, $listing->quantity_on_hand);
    }

    public function test_fee_snapshot_survives_an_admin_rate_change(): void
    {
        [, $buyer, $listing] = $this->market();
        $first = app(Escrow::class)->place($listing, $buyer, 1);
        $this->assertSame(20_000, $first->buyer_fee_kobo);
        $this->assertSame(50_000, $first->seller_fee_kobo);

        $admin = User::factory()->create(['is_admin' => true, 'buys' => false, 'sells' => false]);

        $this->actingAs($admin)->post(route('admin.settings.update'), [
            'buyer_percent' => 20,
            'buyer_floor_naira' => '50.00',
            'seller_percent' => 5,
            'seller_floor_naira' => '100.00',
            'dispute_hours' => 24,
            'payment_hours' => 24,
            'collection_hours' => 48,
            'reason' => 'Defence week trial rate',
        ])->assertRedirect()->assertSessionHas('status');

        $first->refresh();
        $this->assertSame(20_000, $first->buyer_fee_kobo);
        $this->assertSame(50_000, $first->seller_fee_kobo);

        $second = app(Escrow::class)->place($listing->fresh(), $buyer, 1);
        $this->assertSame(200_000, $second->buyer_fee_kobo);
        $this->assertSame(50_000, $second->seller_fee_kobo);
    }

    public function test_farmer_cannot_buy_own_listing_and_handover_then_pays_the_farmer(): void
    {
        [$farmer, $buyer, $listing] = $this->market();
        $farmer->update(['buys' => true]);

        $this->actingAs($farmer)
            ->post(route('orders.store', $listing), ['quantity' => 1])
            ->assertForbidden();
        $this->assertSame(0, $listing->fresh()->quantity_reserved);

        $order = app(Escrow::class)->place($listing->fresh(), $buyer, 1);
        app(Escrow::class)->pay($order, $buyer->fresh());
        app(Escrow::class)->confirm($order->fresh(), $buyer);

        $order->refresh();
        $order->dispute_window_ends_at = now()->subMinute();
        $order->save();

        $this->artisan('orders:settle')->assertSuccessful();

        $order->refresh();
        $this->assertSame(OrderStatus::Completed, $order->status);
        $this->assertSame(950_000, $farmer->wallet->fresh()->available_kobo);
        $this->assertSame(0, $farmer->wallet->fresh()->payable_kobo);
        $this->assertSame(50_000_000 - 1_020_000, $buyer->wallet->fresh()->available_kobo);
        $this->assertSame(79, $listing->fresh()->quantity_on_hand);
    }

    /**
     * @return array{0: User, 1: User, 2: Listing}
     */
    private function market(): array
    {
        $farmer = User::factory()->create(['sells' => true, 'buys' => false]);
        $buyer = User::factory()->create(['sells' => false, 'buys' => true]);
        $buyer->wallet->update(['available_kobo' => 50_000_000]);

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

        return [$farmer, $buyer, $listing];
    }
}
