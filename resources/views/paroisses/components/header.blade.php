@php
    $titles = [
        'dashboard'      => 'Dashboard',
        'agenda'         => 'Agenda',
        'paiement'       => 'Paiement',
        'admin'          => 'Administration',
    ];
    $segments = request()->segments();
    $pageKey = null;
    foreach ($segments as $seg) {
        if (array_key_exists($seg, $titles)) {
            $pageKey = $seg;
            break;
        }
    }
    $pageTitle = $titles[$pageKey] ?? 'Dashboard';
@endphp

<header class="site-header">
    <div class="header-left">
        <button id="menu-toggle" aria-label="Ouvrir le menu" class="menu-btn">
            <i class="fa fa-bars" aria-hidden="true"></i>
        </button>
        <div class="title">
            <a class="name" href="{{ route('paroisses.dashboard', ['uuid' => $paroisse->uuid]) }}">
                {{ $paroisse->name }}
            </a>
            @if($paroisse->slogan)
                <span class="slogan">{{$paroisse->slogan }}</span>
            @endif
        </div>
    </div>

    <div class="header-center">
        <h2>{{$pageTitle}}</h2>
    </div>

    <div class="header-right">
        <div class="user-menu" x-data="{ open: false }" @click.away="open = false">
            <div class="user-menu__toggle" @click="open = !open" :aria-expanded="open.toString()">
                <img src="{{ asset('/images/' . Auth::user()->profileImg) }}" alt="Photo de profil">
                <div>
                    <span>{{ Auth::user()->prenom }} {{ Auth::user()->nom }}</span>
                    <span>{{ Auth::user()->role }}</span>
                </div>
                <svg viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.23 8.27a.75.75 0 01.02-1.06z"/>
                </svg>
            </div>

            <div class="user-menu__dropdown" x-show="open" x-transition x-cloak>
                <a href="{{ route('profile.edit') }}">
                    <svg fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
                    </svg>
                    Mon compte
                </a>
                <a href="{{ route('notifications.index') }}">
                    <svg fill="currentColor" viewBox="0 0 24 24">
                        <path d="M2 4h20v16H2z" fill="none" stroke="currentColor" stroke-width="2"/>
                        <path d="M2 4l10 9 10-9" fill="none" stroke="currentColor" stroke-width="2"/>
                    </svg>
                    <span>Messagerie</span>
                    @if(auth()->user()->notifications && auth()->user()->notifications->count() > 0)
                        <span class="badge">{{auth()->user()->notifications->count()}}</span>
                    @endif
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 17l5-5-5-5M21 12H9M13 3v2M13 19v2M5 4h2a2 2 0 012 2v12a2 2 0 01-2 2H5"/>
                        </svg>
                        Déconnexion
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
