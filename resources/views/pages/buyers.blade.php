@extends('layouts.app')
@section('content')
<section class="panel narrow">
    <p class="kicker">For buyers</p>
    <h1>Pay only into the hold</h1>
    <p>Choose a crop and a state, then order the quantity you can collect. The fee is shown before you pay, and that fee is the one the order keeps. Your money stays with AgroBridge until you confirm collection or read the collection code to the farmer.</p>
    <p>If the produce is not what was listed, open a dispute while the window is open. An administrator then either releases the payment or refunds you, fee included, and both sides can read the note.</p>
    <p><a class="button" href="{{ route('register') }}">Create a buyer account</a></p>
</section>
@endsection
