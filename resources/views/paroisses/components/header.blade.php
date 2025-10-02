@php
    // Tableau associatif pour faire correspondre un segment d’URL à un titre de page
    $titles = [
        'dashboard'      => 'Dashboard',
        'agenda'         => 'Agenda',
        'paiement'       => 'Paiement',
        'admin'          => 'Administration',
    ];

    // Récupère les segments de l’URL actuelle (ex : /paroisses/dashboard → ['paroisses', 'dashboard'])
    $segments = request()->segments();

    $pageKey = null;

    // Parcourt les segments pour trouver le premier correspondant à une clé dans $titles
    foreach ($segments as $seg) {
        if (array_key_exists($seg, $titles)) {
            $pageKey = $seg;
            break; // On s’arrête dès qu’on trouve une correspondance
        }
    }

    // Définit le titre de la page. Par défaut : "Dashboard" si aucun segment ne correspond
    $pageTitle = $titles[$pageKey] ?? 'Dashboard';
@endphp

<header class="site-header">
    <div class="header-left">
        <!-- Bouton pour ouvrir le menu latéral (affiché sur mobile) -->
        <button id="menu-toggle" aria-label="Ouvrir le menu" class="menu-btn">
            <i class="fa fa-bars" aria-hidden="true"></i>
        </button>

        <!-- Titre de la paroisse avec lien vers le dashboard -->
        <div class="title">
            <a class="name" href="{{ route('paroisses.dashboard', ['uuid' => $paroisse->uuid]) }}">
                {{ $paroisse->name }}
            </a>

            <!-- Affiche le slogan si défini -->
            @if($paroisse->slogan)
                <span class="slogan">{{$paroisse->slogan }}</span>
            @endif
        </div>
    </div>
    <!-- Centre de l’en-tête : titre dynamique de la page -->
    <div class="header-center">
        <h2>{{ $pageTitle }}</h2>
    </div>
    <!-- Partie droite de l’en-tête : menu utilisateur -->
    <div class="header-right">
        <!-- ==================== MENU UTILISATEUR ==================== -->
        <div class="user-menu" x-data="{ open: false }" @click.away="open = false">
            {{--
                Bouton qui ouvre le menu utilisateur (avec photo et nom)
                - Utilise Alpine.js pour gérer l’état (open / fermé)
            --}}
            <div class="user-menu__toggle" @click="open = !open" :aria-expanded="open.toString()">
                {{-- Photo de profil de l’utilisateur connecté --}}
                <div class="toggle-left">
                    <img src="{{ asset('/images/' . Auth::user()->profileImg) }}" alt="Photo de profil" width="34px" height="34px">
                </div>

                {{-- Nom complet de l’utilisateur --}}
                <div class="toggle-center">
                    <span class="nom">{{ Auth::user()->prenom }} {{ Auth::user()->nom }}</span>
                </div>

                {{-- Petite flèche vers le bas (icône SVG) pour indiquer le menu déroulant --}}

                <div class="toggle-right">
                    <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.23 8.27a.75.75 0 01.02-1.06z"/>
                    </svg>
                </div>
            </div>

            {{--
                Menu déroulant : affiché uniquement lorsque "open = true"
                - Transition animée grâce à Alpine.js (x-transition)
                - x-cloak empêche le flash au chargement
            --}}
            <div class="user-menu__dropdown " x-show="open" x-transition x-cloak :class="{ 'is-open': open }">
                {{-- Lien vers la page de profil utilisateur --}}
                <div class="profile stagger-item" style="--i:0">
                    <a href="{{ route('profile.edit') }}">
                        <svg fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
                        </svg>
                        <span>Mon compte</span>
                    </a>
                </div>

                {{-- Bouton désactivé pour la messagerie (fonctionnalité à venir) --}}
                <button type="button" disabled class="messagerie stagger-item" style="--i:1">
                    <svg fill="currentColor" viewBox="0 0 24 24">
                        <path d="M2 4h20v16H2z" fill="none" stroke="currentColor" stroke-width="2"/>
                        <path d="M2 4l10 9 10-9" fill="none" stroke="currentColor" stroke-width="2"/>
                    </svg>
                    <span>Messagerie</span>
                    {{-- Badge indiquant le nombre de notifications si l’utilisateur en a --}}
                    @if(auth()->user()->notifications && auth()->user()->notifications->count() > 0)
                        @if(auth()->user()->notifications->count() > 9)
                            <span class="badge">9+</span>
                        @else
                            <span class="badge">{{ auth()->user()->notifications->count() }}</span>
                        @endif
                    @endif
                </button>

                {{-- Bouton de déconnexion via un formulaire POST sécurisé --}}
                <form method="POST" action="{{ route('logout') }}" class="logout stagger-item" style="--i:2">
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
