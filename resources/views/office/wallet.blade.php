@extends('layouts.app')
@section('content')
<section class="panel">
    <p class="kicker">Wallet</p>
    <h1>Your money on AgroBridge</h1>
    <dl class="facts">
        <div><dt>Available</dt><dd>@naira($wallet->available_kobo)</dd></div>
        <div><dt>Owed, not yet paid out</dt><dd>@naira($wallet->payable_kobo)</dd></div>
    </dl>
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
