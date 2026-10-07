@extends('layouts.app')
@section('content')
<section class="panel">
    <p class="kicker">Administrator</p>
    <h1>All orders</h1>
    <p class="lead">Every sale on the platform, whoever the buyer and the farmer are. Rulings are made from Disputes, not from this list.</p>
    @if($orders->isEmpty())
        <p class="empty">No orders yet.</p>
    @else
        <table class="ledger">
            <thead>
                <tr><th>Produce</th><th>Buyer</th><th>Farmer</th><th>Held</th><th>Status</th></tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                    <tr>
                        <td><a href="{{ route('orders.show', $order) }}">{{ $order->listing->crop }}</a></td>
                        <td>{{ $order->buyer->name }}</td>
                        <td>{{ $order->farmer->name }}</td>
                        <td>@naira($order->total_kobo)</td>
                        <td>{{ $order->status->label() }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $orders->links() }}
    @endif
</section>
@endsection
