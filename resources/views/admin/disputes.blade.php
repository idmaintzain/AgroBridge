@extends('layouts.app')
@section('content')
<section class="panel">
    <h1>Disputes</h1>
    <p class="lead">Fees recognised so far: @naira($fees). A ruling is sent to both parties as the note you type.</p>
    <form method="POST" action="{{ route('admin.settle') }}" class="inline">
        @csrf
        <button type="submit">Settle due orders</button>
    </form>
    @if($disputes->isEmpty())
        <p class="empty">No open disputes.</p>
    @else
        @foreach($disputes as $order)
            <article class="dispute">
                <h2>{{ $order->listing->crop }} · order {{ $order->id }}</h2>
                <p>{{ $order->buyer->name }} bought {{ $order->quantity }} from {{ $order->farmer->name }}. Held: @naira($order->total_kobo).</p>
                <p>{{ $order->dispute_reason }}</p>
                <form method="POST" action="{{ route('admin.orders.resolve', $order) }}" class="stack">
                    @csrf
                    <label>Decision
                        <select name="outcome">
                            <option value="release">Release to the farmer</option>
                            <option value="refund">Refund the buyer, fee included</option>
                        </select>
                    </label>
                    <label>Note both parties will see
                        <textarea name="note" rows="3" required minlength="8"></textarea>
                    </label>
                    <button type="submit">Record decision</button>
                </form>
            </article>
        @endforeach
    @endif
</section>
@endsection
