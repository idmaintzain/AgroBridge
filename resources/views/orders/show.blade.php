@extends('layouts.app')
@section('content')
<article class="panel">
    <p class="kicker">Order {{ $order->id }}</p>
    <h1>{{ $order->listing->crop }}</h1>
    <p class="status">{{ $order->status->label() }}</p>
    <dl class="facts">
        <div><dt>Quantity</dt><dd>{{ $order->quantity }} {{ $order->listing->unitWord($order->quantity) }}</dd></div>
        <div><dt>Price locked per unit</dt><dd>@naira($order->unit_price_kobo)</dd></div>
        <div><dt>Produce value</dt><dd>@naira($order->item_kobo)</dd></div>
        <div><dt>Buyer fee, snapshotted</dt><dd>@naira($order->buyer_fee_kobo)</dd></div>
        <div><dt>Buyer pays</dt><dd>@naira($order->total_kobo)</dd></div>
        <div><dt>Farmer fee, snapshotted</dt><dd>@naira($order->seller_fee_kobo)</dd></div>
        <div><dt>Farmer receives</dt><dd>@naira($order->payout_kobo)</dd></div>
        <div><dt>Buyer</dt><dd>{{ $order->buyer->name }}</dd></div>
        <div><dt>Farmer</dt><dd>{{ $order->farmer->name }}</dd></div>
        @if($order->payment_deadline_at && $order->status === \App\Enums\OrderStatus::AwaitingPayment)
            <div><dt>Pay before</dt><dd>{{ $order->payment_deadline_at->format('j M Y, H:i') }}</dd></div>
        @endif
        @if($order->collection_deadline_at && $order->status === \App\Enums\OrderStatus::Paid)
            <div><dt>Collect before</dt><dd>{{ $order->collection_deadline_at->format('j M Y, H:i') }}</dd></div>
        @endif
        @if($order->dispute_window_ends_at && $order->status === \App\Enums\OrderStatus::Delivered)
            <div><dt>Dispute window ends</dt><dd>{{ $order->dispute_window_ends_at->format('j M Y, H:i') }}</dd></div>
        @endif
    </dl>

    @if($code)
        <p class="code">Collection code for the farmer: <strong>{{ $code }}</strong></p>
        <p>Read this out at collection. The farmer cannot see it on their own screen.</p>
    @endif

    @if($order->dispute_reason)
        <p><strong>Buyer’s dispute.</strong> {{ $order->dispute_reason }}</p>
    @endif
    @if($order->resolution_note)
        <p><strong>Administrator’s note.</strong> {{ $order->resolution_note }}</p>
    @endif

    @if(auth()->id() === $order->buyer_id && $order->status === \App\Enums\OrderStatus::AwaitingPayment)
        <form method="POST" action="{{ route('orders.pay', $order) }}" class="inline">
            @csrf
            <button type="submit">Pay @naira($order->total_kobo) into escrow</button>
        </form>
        <form method="POST" action="{{ route('orders.cancel', $order) }}" class="inline">
            @csrf
            <button type="submit" class="quiet">Cancel reservation</button>
        </form>
    @endif

    @if(auth()->id() === $order->buyer_id && $order->status === \App\Enums\OrderStatus::Paid)
        <form method="POST" action="{{ route('orders.confirm', $order) }}">
            @csrf
            <button type="submit">I have collected this produce</button>
        </form>
    @endif

    @if(auth()->id() === $order->farmer_id && $order->status === \App\Enums\OrderStatus::Paid)
        <form method="POST" action="{{ route('orders.code', $order) }}" class="stack">
            @csrf
            <label>Collection code the buyer reads to you
                <input name="code" autocomplete="off" required>
            </label>
            <button type="submit">Record handover</button>
        </form>
        <p class="aside">There is no button that pays you because you say it was delivered.</p>
    @endif

    @if(auth()->id() === $order->buyer_id && $order->status === \App\Enums\OrderStatus::Delivered)
        <form method="POST" action="{{ route('orders.dispute', $order) }}" class="stack">
            @csrf
            <label>What went wrong
                <textarea name="reason" rows="3" required minlength="10"></textarea>
            </label>
            <button type="submit" class="quiet">Open a dispute</button>
        </form>
    @endif

    <h2>Money movements</h2>
    @if($order->ledgerEntries->isEmpty())
        <p class="aside">Nothing has been held yet.</p>
    @else
        <table class="ledger">
            <thead>
                <tr><th>Account</th><th>Movement</th><th>Amount</th><th>Note</th></tr>
            </thead>
            <tbody>
                @foreach($order->ledgerEntries as $entry)
                    <tr>
                        <td>{{ str_replace('_', ' ', $entry->account) }}</td>
                        <td>{{ $entry->direction }}</td>
                        <td>@naira($entry->amount_kobo)</td>
                        <td>{{ $entry->memo }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</article>
@endsection
