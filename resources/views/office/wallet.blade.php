@extends('layouts.app')
@section('content')
<section class="panel">
    <p class="kicker">Wallet</p>
    <h1>Your money on AgroBridge</h1>
    <p class="lead">This is demonstration naira. Top-ups, withdrawals, and transfers do not touch a real bank. Produce payments still leave Available and stay with AgroBridge until collection is proved. Owed payouts cannot be spent until they move into Available.</p>
    <dl class="facts">
        <div><dt>Available</dt><dd>@naira($wallet->available_kobo)</dd></div>
        <div><dt>Owed, not yet paid out</dt><dd>@naira($wallet->payable_kobo)</dd></div>
    </dl>
    <div class="wallet-ops">
        <form method="POST" action="{{ route('wallet.fund') }}" class="stack">
            <h2>Top up</h2>
            @csrf
            <label>Amount (naira)
                <input name="amount" value="{{ old('amount') }}" inputmode="decimal" required>
            </label>
            <button type="submit">Add funds</button>
        </form>
        <form method="POST" action="{{ route('wallet.withdraw') }}" class="stack">
            <h2>Withdraw</h2>
            @csrf
            <label>Amount (naira)
                <input name="amount" inputmode="decimal" required>
            </label>
            <button type="submit">Withdraw</button>
        </form>
        <form method="POST" action="{{ route('wallet.transfer') }}" class="stack">
            <h2>Transfer</h2>
            @csrf
            <label>Recipient email
                <input type="email" name="email" value="{{ old('email') }}" required>
            </label>
            <label>Amount (naira)
                <input name="amount" inputmode="decimal" required>
            </label>
            <button type="submit">Send</button>
        </form>
    </div>
    @if($entries->isEmpty())
        <p class="empty">No money has moved on this account yet.</p>
    @else
        <table class="ledger">
            <thead>
                <tr><th>When</th><th>Account</th><th>Movement</th><th>Amount</th><th>Note</th></tr>
            </thead>
            <tbody>
                @foreach($entries as $entry)
                    <tr>
                        <td>{{ $entry->created_at->format('j M Y, H:i') }}</td>
                        <td>{{ str_replace('_', ' ', $entry->account) }}</td>
                        <td>{{ $entry->direction }}</td>
                        <td>@naira($entry->amount_kobo)</td>
                        <td>{{ $entry->memo }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $entries->links() }}
    @endif
</section>
@endsection
