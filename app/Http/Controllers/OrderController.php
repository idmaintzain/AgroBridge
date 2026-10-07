<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Order;
use App\Services\Escrow;
use App\Services\EscrowException;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function purchases(Request $request)
    {
        $orders = Order::query()
            ->with('listing')
            ->where('buyer_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return view('orders.index', [
            'orders' => $orders,
            'heading' => 'Purchases',
            'intro' => 'Money stays with AgroBridge until you confirm that the produce was collected.',
        ]);
    }

    public function sales(Request $request)
    {
        abort_unless($request->user()->sells, 403);

        $orders = Order::query()
            ->with('listing')
            ->where('farmer_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return view('orders.index', [
            'orders' => $orders,
            'heading' => 'Sales',
            'intro' => 'You are paid only after the buyer confirms collection, or after you enter the code they read out. Claiming delivery does not release the money.',
        ]);
    }

    public function show(Request $request, Order $order)
    {
        abort_unless($order->involves($request->user()), 403);
        $order->load(['listing', 'buyer', 'farmer', 'ledgerEntries']);

        return view('orders.show', [
            'order' => $order,
            'code' => $request->user()->id === $order->buyer_id ? $order->plainCode() : null,
        ]);
    }

    public function store(Request $request, Listing $listing, Escrow $escrow)
    {
        abort_unless($request->user()->buys, 403);
        abort_unless($request->user()->id !== $listing->user_id, 403);

        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $order = $escrow->place($listing, $request->user(), (int) $data['quantity']);
        } catch (EscrowException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('orders.show', $order)->with('status', 'Quantity reserved. Pay from your wallet to hold the money with AgroBridge.');
    }

    public function pay(Request $request, Order $order, Escrow $escrow)
    {
        abort_unless($request->user()->id === $order->buyer_id, 403);

        try {
            $escrow->pay($order, $request->user());
        } catch (EscrowException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Payment is held. The farmer can hand over the produce, but cannot release the money.');
    }

    public function confirm(Request $request, Order $order, Escrow $escrow)
    {
        abort_unless($request->user()->id === $order->buyer_id, 403);

        try {
            $escrow->confirm($order, $request->user());
        } catch (EscrowException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Handover recorded. The dispute window is running. The farmer is paid when it closes.');
    }

    public function code(Request $request, Order $order, Escrow $escrow)
    {
        abort_unless($request->user()->id === $order->farmer_id, 403);

        $data = $request->validate([
            'code' => ['required', 'string', 'max:12'],
        ]);

        try {
            $escrow->submitCode($order, $request->user(), $data['code']);
        } catch (EscrowException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Collection code accepted. The dispute window is now running.');
    }

    public function dispute(Request $request, Order $order, Escrow $escrow)
    {
        abort_unless($request->user()->id === $order->buyer_id, 403);

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        try {
            $escrow->dispute($order, $request->user(), $data['reason']);
        } catch (EscrowException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Dispute opened. The held payment stays put until an administrator decides.');
    }

    public function cancel(Request $request, Order $order, Escrow $escrow)
    {
        abort_unless($request->user()->id === $order->buyer_id, 403);

        try {
            $escrow->cancel($order, $request->user());
        } catch (EscrowException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Order cancelled. The reserved quantity is back on the listing.');
    }
}
