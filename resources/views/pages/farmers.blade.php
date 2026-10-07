@extends('layouts.app')
@section('content')
<section class="panel narrow">
    <p class="kicker">For farmers</p>
    <h1>List the quantity you can actually sell</h1>
    <p>Create an account that can sell, or both sell and buy. Publish a listing with the unit buyers already use: kilogram, bag, basket, or crate. You are paid only after collection is proved. A buyer may take part of the heap; the remainder stays for sale.</p>
    <p>You never see the collection code. The buyer reads it to you at the farm gate. Typing that code records the handover. It does not, by itself, move the money before the dispute window closes.</p>
    <p><a class="button" href="{{ route('register') }}">Create a farmer account</a></p>
</section>
@endsection
