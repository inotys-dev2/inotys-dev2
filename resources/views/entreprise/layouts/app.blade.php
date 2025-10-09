{{--
    Page layout principale du tableau de bord pour les entreprises.
    Ce layout sert de structure globale : il inclut le header, la sidebar,
    le contenu principal (via @yield) et le footer.
--}}

<!DOCTYPE html>
<html lang="fr">
<head>
    {{-- Définition de l'encodage et du titre dynamique selon le nom de l’entreprise --}}
    <meta charset="UTF-8">
    <title>Dashboard {{ $entreprise->name }}</title>

    {{-- Importation de la police Google Roboto --}}
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">

    {{-- Importation de la bibliothèque d'icônes Font Awesome --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    {{-- Token CSRF pour la protection des formulaires et requêtes Ajax --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Inclusion du fichier CSS principal généré par Vite --}}
    @vite('resources/css/app.css')

    @php
        // Récupération du thème et du type d’accès de l’utilisateur connecté
        $theme = auth()->user()->theme;
        $type = auth()->user()->access;

        // Chemin vers le fichier CSS personnalisé selon le thème et le type d’accès
        $paths = [
          "resources/css/{$theme}/{$type}/main.css",
        ];
    @endphp

    {{-- Inclusion conditionnelle du fichier CSS spécifique si le fichier existe réellement --}}
    @foreach ($paths as $path)
        @if(file_exists(resource_path('css/'. $theme .'/'. $type .'/'. basename($path))))
            @vite($path)
        @endif
    @endforeach
</head>
<body>

@include('entreprise.components.header')

<!-- Structure principale de la page -->
<div class="layout">
    <!-- Sidebar visible sur grand écran -->
    <div class="sidebar-app">
        @include('entreprise.components.sidebar')
    </div>

    <!-- Navbar visible sur petit écran -->
    <div class="navbar-app">
        @include('entreprise.components.navbars')
    </div>

    <!-- Contenu principal -->
    <div class="main-content">
        @yield('content') <!-- Contenu injecté par les vues enfants -->
    </div>
</div>
{{--
    Inclusion du footer commun.
    Peut contenir les informations légales, les liens de contact, etc.
--}}
@include('entreprise.components.footer')

{{--
    Inclusion du script Alpine.js pour la gestion de l’interactivité côté client
    (par exemple, pour les menus déroulants ou les composants dynamiques).
--}}
<script src="https://unpkg.com/alpinejs@3.14.9/dist/cdn.min.js"></script>

{{-- Inclusion du fichier JavaScript principal généré par Vite --}}
@vite('resources/js/app.js')
</body>
</html>
