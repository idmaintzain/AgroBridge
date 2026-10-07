@extends('layouts.app')

@section('content')
<section class="hero">
    <div class="hero-copy">
        <p class="kicker">Farm gate to buyer, with the money held in between</p>
        <h1>Produce listed by quantity. Payment stays with <em>AgroBridge</em> until the handover is real.</h1>
        <ul class="trust">
            <li>Secure payments</li>
            <li>Verified farmers and buyers</li>
            <li>Transparent and trusted</li>
        </ul>
    </div>
    <div class="hero-visual">
        <img src="{{ \App\Support\ProducePhotos::hero() }}" alt="A farmer holding a crate of fresh vegetables">
        <form class="filter-card" method="GET" action="{{ route('market') }}">
            <label>
                <span class="field-label"><span class="leaf" aria-hidden="true"></span> Crop</span>
                <select name="crop">
                    <option value="">All crops</option>
                    @foreach($crops as $name)
                        <option value="{{ $name }}" @selected($crop === $name)>{{ $name }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span class="field-label"><span class="pin" aria-hidden="true"></span> State</span>
                <select name="state">
                    <option value="">All states</option>
                    @foreach($states as $name)
                        <option value="{{ $name }}" @selected($state === $name)>{{ $name }}</option>
                    @endforeach
                </select>
            </label>
            <button type="submit">Show listings</button>
        </form>
    </div>
</section>

<section class="shelf" id="listings">
    <div class="shelf-head">
        <h2>Available Produce</h2>
        <a href="{{ route('market') }}">View all listings</a>
    </div>

    @if($listings->isEmpty())
        <p class="empty">Nothing is listed for that filter. Farmers with unsold quantity appear here.</p>
    @else
        <div class="grid">
            @foreach($listings as $listing)
                <a class="produce" href="{{ route('listings.show', $listing) }}">
                    <span class="photo">
                        <img src="{{ $listing->photoUrl() }}" alt="{{ $listing->crop }}">
                        @if($listing->availableQuantity() > 0)
                            <span class="badge">Available</span>
                        @endif
                    </span>
                    <span class="body">
                        <span class="name-row">
                            <span class="crop">{{ $listing->crop }}</span>
                            <span class="go" aria-hidden="true">&rsaquo;</span>
                        </span>
                        <span class="price">@naira($listing->price_per_unit_kobo) <span>per {{ $listing->unit->value }}</span></span>
                        <span class="meta">{{ number_format($listing->availableQuantity()) }} {{ $listing->unitWord($listing->availableQuantity()) }} available</span>
                        <span class="meta">{{ $listing->lga }}, {{ $listing->state }}</span>
                        <span class="meta">Collect by {{ $listing->collect_by->format('j M Y') }}</span>
                    </span>
                </a>
            @endforeach
        </div>
        {{ $listings->links() }}
    @endif
</section>
@endsection
