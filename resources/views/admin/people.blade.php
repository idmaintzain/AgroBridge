@extends('layouts.app')
@section('content')
<section class="panel">
    <p class="kicker">Administrator</p>
    <h1>People</h1>
    <p class="lead">Every account, the level it was given, and the wallet it holds. This screen does not change a password or a role.</p>
    <table class="ledger">
        <thead>
            <tr><th>Name</th><th>Email</th><th>Level</th><th>Available</th></tr>
        </thead>
        <tbody>
            @foreach($people as $person)
                <tr>
                    <td>{{ $person->name }}</td>
                    <td>{{ $person->email }}</td>
                    <td>{{ $person->roleLabel() }}</td>
                    <td>@naira($person->wallet->available_kobo)</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $people->links() }}
</section>
@endsection
