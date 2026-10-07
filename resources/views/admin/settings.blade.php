@extends('layouts.app')
@section('content')
<section class="panel narrow">
    <h1>Fees and windows</h1>
    <p class="lead">A change applies to the next order only. An order already placed keeps the numbers it was shown.</p>
    <form method="POST" action="{{ route('admin.settings.update') }}" class="stack">
        @csrf
        <label>Buyer fee percent
            <input type="number" name="buyer_percent" min="0" max="30" value="{{ old('buyer_percent', $buyerPercent) }}" required>
        </label>
        <label>Buyer fee floor (naira)
            <input name="buyer_floor_naira" value="{{ old('buyer_floor_naira', number_format($buyerFloor / 100, 2, '.', '')) }}" required>
        </label>
        <label>Farmer fee percent
            <input type="number" name="seller_percent" min="0" max="30" value="{{ old('seller_percent', $sellerPercent) }}" required>
        </label>
        <label>Farmer fee floor (naira)
            <input name="seller_floor_naira" value="{{ old('seller_floor_naira', number_format($sellerFloor / 100, 2, '.', '')) }}" required>
        </label>
        <label>Dispute window (hours)
            <input type="number" name="dispute_hours" min="1" max="168" value="{{ old('dispute_hours', $disputeHours) }}" required>
        </label>
        <label>Payment window (hours)
            <input type="number" name="payment_hours" min="1" max="168" value="{{ old('payment_hours', $paymentHours) }}" required>
        </label>
        <label>Collection window (hours)
            <input type="number" name="collection_hours" min="1" max="336" value="{{ old('collection_hours', $collectionHours) }}" required>
        </label>
        <label>Reason for this change
            <textarea name="reason" rows="3" required minlength="8">{{ old('reason') }}</textarea>
        </label>
        <button type="submit">Save settings</button>
    </form>
</section>
@endsection
