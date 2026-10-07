@extends('layouts.app')
@section('content')
<section class="panel narrow">
    <p class="kicker">Contact</p>
    <h1>A postgraduate demonstration</h1>
    <p>AgroBridge is the project of Olatunji Ayodeji Peter, matriculation number LCU/PG/0010452, for the Postgraduate Diploma in Software Engineering at Lead City University, Ibadan.</p>
    <p>This site does not deliver messages to a farm desk. Questions about the project go through the department. On the sign-in page, the defence accounts are listed with the demonstration password.</p>
    <p><a class="button" href="{{ route('login') }}">Go to sign in</a></p>
</section>
@endsection
