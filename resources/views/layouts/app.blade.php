<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'AgroBridge' }} · Fresh produce marketplace</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/agrobridge.css') }}">
</head>
<body @class(['is-office' => auth()->check()])>
    @auth
        <div class="shell">
            <aside class="side">
                <a class="brand" href="{{ route('desk') }}">
                    <span class="mark" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="18" height="18"><path fill="currentColor" d="M12 3c2.5 3 5 6.2 5 9.2A5 5 0 1 1 7 12.2C7 9.2 9.5 6 12 3z"/></svg>
                    </span>
                    AgroBridge
                </a>
                <p class="side-role">{{ auth()->user()->roleLabel() }}</p>
                <p class="side-name">{{ auth()->user()->name }}</p>
                <nav class="side-nav">
                    @foreach(\App\Support\OfficeMenu::for(auth()->user()) as $item)
                        <a href="{{ route($item['route']) }}" @class(['is-on' => request()->routeIs($item['match'])])>{{ $item['label'] }}</a>
                    @endforeach
                </nav>
                <div class="side-foot">
                    @unless(auth()->user()->is_admin)
                        <p class="side-balance">@naira(auth()->user()->wallet->available_kobo)</p>
                    @endunless
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="quiet">Sign out</button>
                    </form>
                </div>
            </aside>
            <div class="sheet">
                @include('layouts.flashes')
                @yield('content')
            </div>
        </div>
    @else
        <header class="top">
            <a class="brand" href="{{ route('market') }}">
                <span class="mark" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="18" height="18"><path fill="currentColor" d="M12 3c2.5 3 5 6.2 5 9.2A5 5 0 1 1 7 12.2C7 9.2 9.5 6 12 3z"/></svg>
                </span>
                AgroBridge
            </a>
            <nav>
                <a href="{{ route('market') }}" @class(['is-on' => request()->routeIs('market')])>Market</a>
                <a href="{{ route('about') }}" @class(['is-on' => request()->routeIs('about')])>About</a>
                <a href="{{ route('how') }}" @class(['is-on' => request()->routeIs('how')])>How it Works</a>
                <a href="{{ route('farmers') }}" @class(['is-on' => request()->routeIs('farmers')])>For Farmers</a>
                <a href="{{ route('buyers') }}" @class(['is-on' => request()->routeIs('buyers')])>For Buyers</a>
                <a href="{{ route('contact') }}" @class(['is-on' => request()->routeIs('contact')])>Contact</a>
            </nav>
            <div class="account">
                <a class="signin" href="{{ route('login') }}">Sign in</a>
                <a class="button" href="{{ route('register') }}">Create account</a>
            </div>
        </header>
        <main>
            @include('layouts.flashes')
            @yield('content')
        </main>
        <footer>
            <p>AgroBridge holds the buyer’s payment and releases it to the farmer only after collection is proved.</p>
            <p>Postgraduate Diploma project, Lead City University. Olatunji Ayodeji Peter · LCU/PG/0010452</p>
        </footer>
    @endauth
</body>
</html>
