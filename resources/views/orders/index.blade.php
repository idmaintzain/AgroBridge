@extends('layouts.app')
@section('content')
<section>
    <h1>{{ $heading }}</h1>
    <p class="lead">{{ $intro }}</p>
    @if($orders->isEmpty())
        <p class="empty">No orders yet.</p>
    @else
        <table class="ledger">
            <thead>
                <tr>
                    <th>Produce</th>
                    <th>Qty</th>
                    <th>Held / due</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                    <tr>
                        <td><a href="{{ route('orders.show', $order) }}">{{ $order->listing->crop }}</a></td>
                        <td>{{ $order->quantity }} {{ $order->listing->unitWord($order->quantity) }}</td>
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
