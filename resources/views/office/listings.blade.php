@extends('layouts.app')
@section('content')
<section class="panel">
    <p class="kicker">Farmer</p>
    <h1>My produce</h1>
    <p class="lead">Only listings you created. Buyers see them on the market while quantity remains.</p>
    @if($listings->isEmpty())
        <p class="empty">You have not listed anything yet.</p>
    @else
        <table class="ledger">
            <thead>
                <tr>
                    <th></th>
                    <th>Crop</th>
                    <th>Available</th>
                    <th>Price</th>
                    <th>Place</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($listings as $listing)
                    <tr>
                        <td><img class="thumb" src="{{ $listing->photoUrl() }}" alt=""></td>
                        <td>{{ $listing->crop }}</td>
                        <td>{{ number_format($listing->availableQuantity()) }} {{ $listing->unitWord($listing->availableQuantity()) }}</td>
                        <td>@naira($listing->price_per_unit_kobo)</td>
                        <td>{{ $listing->lga }}, {{ $listing->state }}</td>
                        <td><a href="{{ route('listings.edit', $listing) }}">Edit</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $listings->links() }}
    @endif
</section>
@endsection
