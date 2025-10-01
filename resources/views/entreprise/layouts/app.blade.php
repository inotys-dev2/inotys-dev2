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

{{--
    Inclusion du header commun à toutes les pages entreprises.
    Contient généralement le logo, le nom de l’entreprise, le menu utilisateur, etc.
--}}
@include('entreprise.components.header')

<div class="layout">
    {{--
        Inclusion de la sidebar (menu latéral).
        Contient les liens de navigation vers les différentes sections du dashboard.
    --}}
    @include('entreprise.components.sidebar')

    {{--
        Zone principale de contenu.
        Le contenu spécifique à chaque page sera injecté ici avec @yield('content').
    --}}
    <div class="main-content">
        @yield('content')
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
