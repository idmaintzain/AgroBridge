@extends('layouts.app')
@section('content')
<section class="panel narrow">
    <h1>Sign in</h1>
    <aside class="demo">
        <p>Defence accounts, all using the password <strong>password</strong></p>
        <ul>
            <li>farmer@agrobridge.test — farmer only. Wallet ₦20,000.00</li>
            <li>buyer@agrobridge.test — buyer only. Wallet ₦500,000.00</li>
            <li>ada@agrobridge.test — farmer and buyer. Wallet ₦150,000.00</li>
            <li>admin@agrobridge.test — administrator. Disputes, fees, and settlement</li>
        </ul>
        <div class="demo-actions">
            <form method="POST" action="{{ route('demo.login', 'buyer') }}">@csrf<button type="submit">Continue as the buyer</button></form>
            <form method="POST" action="{{ route('demo.login', 'farmer') }}">@csrf<button type="submit">Continue as the farmer</button></form>
            <form method="POST" action="{{ route('demo.login', 'both') }}">@csrf<button type="submit">Continue as farmer and buyer</button></form>
            <form method="POST" action="{{ route('demo.login', 'admin') }}">@csrf<button type="submit" class="quiet">Continue as admin</button></form>
        </div>
    </aside>
    <form method="POST" action="{{ route('login') }}" class="stack">
        @csrf
        <label>Email
            <input type="email" name="email" value="{{ old('email') }}" required autofocus>
        </label>
        <label>Password
            <input type="password" name="password" required>
        </label>
        <label class="check"><input type="checkbox" name="remember" value="1"> Keep me signed in</label>
        <button type="submit">Sign in</button>
    </form>
    <p class="aside">No account yet? <a href="{{ route('register') }}">Create one</a></p>
</section>
@endsection
