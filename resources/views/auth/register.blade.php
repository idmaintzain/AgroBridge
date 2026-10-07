@extends('layouts.app')
@section('content')
<section class="panel narrow">
    <h1>Create an account</h1>
    <form method="POST" action="{{ route('register') }}" class="stack">
        @csrf
        <label>Full name
            <input name="name" value="{{ old('name') }}" required>
        </label>
        <label>Email
            <input type="email" name="email" value="{{ old('email') }}" required>
        </label>
        <label>Password
            <input type="password" name="password" required>
        </label>
        <label>Confirm password
            <input type="password" name="password_confirmation" required>
        </label>
        <fieldset>
            <legend>How you will use AgroBridge</legend>
            <label class="check"><input type="radio" name="role" value="buy" @checked(old('role', 'buy') === 'buy')> Buy produce</label>
            <label class="check"><input type="radio" name="role" value="sell" @checked(old('role') === 'sell')> Sell produce</label>
            <label class="check"><input type="radio" name="role" value="both" @checked(old('role') === 'both')> Both</label>
        </fieldset>
        <button type="submit">Create account</button>
    </form>
</section>
@endsection
