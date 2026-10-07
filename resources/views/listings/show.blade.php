@extends('layouts.app')
@section('content')
<article class="detail">
    <img class="detail-photo" src="{{ $listing->photoUrl() }}" alt="{{ $listing->crop }}">
    <div>
        <p class="kicker">{{ $listing->lga }}, {{ $listing->state }}</p>
        <h1>{{ $listing->crop }}</h1>
        <p class="price large">@naira($listing->price_per_unit_kobo) <span>per {{ $listing->unit->value }}</span></p>
        <p>{{ number_format($listing->availableQuantity()) }} {{ $listing->unitWord($listing->availableQuantity()) }} available. Farmer: {{ $listing->farmer->name }}. Collect by {{ $listing->collect_by->format('j M Y') }}.</p>
        @if($listing->description)
            <p>{{ $listing->description }}</p>
        @endif

        @if($quote)
            <dl class="facts">
                <div><dt>Buyer fee on one {{ $listing->unit->value }}</dt><dd>@naira($quote['buyer_fee_kobo'])</dd></div>
                <div><dt>Farmer keeps, after fee</dt><dd>@naira($quote['payout_kobo'])</dd></div>
                <div><dt>Dispute window</dt><dd>{{ $quote['dispute_window_hours'] }} hours after handover</dd></div>
            </dl>
        @endif

        @auth
            @if(auth()->id() === $listing->user_id)
                <a class="button" href="{{ route('listings.edit', $listing) }}">Edit this listing</a>
            @elseif(auth()->user()->buys && $listing->availableQuantity() > 0 && $listing->is_active)
                <form method="POST" action="{{ route('orders.store', $listing) }}" class="stack buy">
                    @csrf
                    <label>Quantity to reserve
                        <input type="number" name="quantity" min="1" max="{{ $listing->availableQuantity() }}" value="{{ old('quantity', 1) }}" required>
                    </label>
                    <button type="submit">Reserve and continue to payment</button>
                </form>
            @endif
        @else
            <p><a href="{{ route('login') }}">Sign in</a> to buy this produce. The payment is held until collection is confirmed.</p>
        @endauth
    </div>
</article>
@endsection
