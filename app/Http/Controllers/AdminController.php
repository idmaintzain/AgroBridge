<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\LedgerEntry;
use App\Models\Order;
use App\Services\Escrow;
use App\Services\EscrowException;
use App\Services\Settings;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function disputes()
    {
        $disputes = Order::query()
            ->with(['listing', 'buyer', 'farmer'])
            ->where('status', OrderStatus::Disputed)
            ->oldest('dispute_opened_at')
            ->get();

        $fees = (int) LedgerEntry::query()
            ->where('account', 'platform_fee')
            ->where('direction', 'credit')
            ->sum('amount_kobo');

        return view('admin.disputes', [
            'disputes' => $disputes,
            'fees' => $fees,
        ]);
    }

    public function resolve(Request $request, Order $order, Escrow $escrow)
    {
        $data = $request->validate([
            'outcome' => ['required', Rule::in(['release', 'refund'])],
            'note' => ['required', 'string', 'min:8', 'max:1000'],
        ]);

        try {
            $escrow->resolve($order, $request->user(), $data['outcome'], $data['note']);
        } catch (EscrowException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Dispute resolved. Both parties can read your note on the order.');
    }

    public function people()
    {
        $people = \App\Models\User::query()->with('wallet')->orderBy('name')->paginate(20);

        return view('admin.people', ['people' => $people]);
    }

    public function orders()
    {
        $orders = Order::query()->with(['listing', 'buyer', 'farmer'])->latest()->paginate(20);

        return view('admin.orders', ['orders' => $orders]);
    }

    public function settings(Settings $settings)
    {
        return view('admin.settings', [
            'buyerPercent' => $settings->int('fees.buyer_percent'),
            'buyerFloor' => $settings->int('fees.buyer_floor_kobo'),
            'sellerPercent' => $settings->int('fees.seller_percent'),
            'sellerFloor' => $settings->int('fees.seller_floor_kobo'),
            'disputeHours' => $settings->int('windows.dispute_hours'),
            'paymentHours' => $settings->int('windows.payment_hours'),
            'collectionHours' => $settings->int('windows.collection_hours'),
        ]);
    }

    public function updateSettings(Request $request, Settings $settings)
    {
        $data = $request->validate([
            'buyer_percent' => ['required', 'integer', 'min:0', 'max:30'],
            'buyer_floor_naira' => ['required', 'regex:/^\d+(\.\d{1,2})?$/'],
            'seller_percent' => ['required', 'integer', 'min:0', 'max:30'],
            'seller_floor_naira' => ['required', 'regex:/^\d+(\.\d{1,2})?$/'],
            'dispute_hours' => ['required', 'integer', 'min:1', 'max:168'],
            'payment_hours' => ['required', 'integer', 'min:1', 'max:168'],
            'collection_hours' => ['required', 'integer', 'min:1', 'max:336'],
            'reason' => ['required', 'string', 'min:8', 'max:500'],
        ]);

        $pairs = [
            'fees.buyer_percent' => (string) $data['buyer_percent'],
            'fees.buyer_floor_kobo' => (string) \App\Support\Money::toKobo($data['buyer_floor_naira']),
            'fees.seller_percent' => (string) $data['seller_percent'],
            'fees.seller_floor_kobo' => (string) \App\Support\Money::toKobo($data['seller_floor_naira']),
            'windows.dispute_hours' => (string) $data['dispute_hours'],
            'windows.payment_hours' => (string) $data['payment_hours'],
            'windows.collection_hours' => (string) $data['collection_hours'],
        ];

        foreach ($pairs as $key => $value) {
            $settings->set($key, $value, $request->user(), $data['reason']);
        }

        return back()->with('status', 'Settings saved. Orders already placed keep the fees and windows they were created with.');
    }

    public function settle(Escrow $escrow)
    {
        $changed = $escrow->settle();

        return back()->with('status', "Settlement finished. {$changed} orders moved.");
    }
}
