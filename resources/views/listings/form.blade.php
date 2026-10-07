@extends('layouts.app')
@section('content')
@php
    $checked = session()->hasOldInput()
        ? (bool) old('is_active')
        : ($listing->exists ? $listing->is_active : true);
    $price = old('price_naira', $listing->price_per_unit_kobo ? number_format($listing->price_per_unit_kobo / 100, 2, '.', '') : '');
@endphp
<section class="panel narrow">
    <h1>{{ $listing->exists ? 'Edit listing' : 'List produce' }}</h1>
    <form method="POST" action="{{ $listing->exists ? route('listings.update', $listing) : route('listings.store') }}" class="stack" enctype="multipart/form-data">
        @csrf
        @if($listing->exists)
            @method('PUT')
        @endif
        <label>Crop
            <input name="crop" list="crops" value="{{ old('crop', $listing->crop) }}" required>
            <datalist id="crops">
                @foreach($crops as $name)
                    <option value="{{ $name }}"></option>
                @endforeach
            </datalist>
        </label>
        <label>Unit
            <select name="unit" required>
                @foreach($units as $unit)
                    <option value="{{ $unit->value }}" @selected(old('unit', $listing->unit?->value) === $unit->value)>{{ $unit->label() }}</option>
                @endforeach
            </select>
        </label>
        <label>Quantity on hand
            <input type="number" min="0" name="quantity_on_hand" value="{{ old('quantity_on_hand', $listing->quantity_on_hand) }}" required>
        </label>
        <label>Price per unit (naira)
            <input name="price_naira" inputmode="decimal" value="{{ $price }}" required>
        </label>
        <label>State
            <select name="state" required>
                @foreach($states as $name)
                    <option value="{{ $name }}" @selected(old('state', $listing->state) === $name)>{{ $name }}</option>
                @endforeach
            </select>
        </label>
        <label>LGA
            <input name="lga" value="{{ old('lga', $listing->lga) }}" required>
        </label>
        <label>Collect by
            <input type="date" name="collect_by" value="{{ old('collect_by', $listing->collect_by?->format('Y-m-d')) }}" required>
        </label>
        <label>Note for buyers
            <textarea name="description" rows="4">{{ old('description', $listing->description) }}</textarea>
        </label>
        <label>Photograph
            @if($listing->exists)
                <img class="upload-preview" src="{{ $listing->photoUrl() }}" alt="Current photograph of {{ $listing->crop }}">
            @endif
            <input type="file" name="image" accept="image/jpeg,image/png,image/webp" @required(! $listing->exists)>
            <span class="aside">{{ $listing->exists ? 'Choose a new file only if you want to replace this photograph. JPG, PNG, or WebP, up to 4 MB.' : 'A photograph is required. JPG, PNG, or WebP, up to 4 MB.' }}</span>
        </label>
        @if($listing->image_path)
            <label class="check">
                <input type="checkbox" name="remove_image" value="1">
                Remove this photograph and use the standard crop picture
            </label>
        @endif
        <label class="check">
            <input type="checkbox" name="is_active" value="1" @checked($checked)>
            Visible on the market
        </label>
        <button type="submit">{{ $listing->exists ? 'Save listing' : 'Publish listing' }}</button>
    </form>
</section>
@endsection
