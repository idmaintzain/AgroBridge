@extends('layouts.app')
@section('content')
<section class="panel">
    <p class="kicker">{{ $role }}</p>
    <h1>Desk</h1>
    <div class="facts">
        @foreach($cards as $card)
            <div>
                <dt>{{ $card['label'] }}</dt>
                <dd>{{ $card['value'] }}</dd>
                <p class="meta">{{ $card['note'] }}</p>
            </div>
        @endforeach
    </div>
</section>
@endsection
