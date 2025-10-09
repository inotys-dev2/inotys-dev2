<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <!-- Définition du charset et du titre de la page -->
    <meta charset="UTF-8">
    <title>Dashboard</title>

    <!-- Police et icônes -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- Jeton CSRF pour la sécurité des requêtes -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Feuille de style principale générée par Vite -->
    @vite('resources/css/app.css')

    <!-- Chargement conditionnel du thème utilisateur -->
    @php
        $theme = auth()->user()->theme;  // Récupère le thème choisi par l'utilisateur
        $type = auth()->user()->access;  // Récupère le type d'accès de l'utilisateur (rôle)
        $paths = [
          "resources/css/{$theme}/{$type}/main.css", // Chemin CSS basé sur le thème et le rôle
        ];
    @endphp

        <!-- Inclusion dynamique du fichier CSS correspondant si présent -->
    @foreach ($paths as $path)
        @if(file_exists(resource_path('css/'. $theme .'/'. $type .'/'. basename($path))))
            @vite($path)
        @endif
    @endforeach

</head>
<body>

<!-- Inclusion de l'en-tête -->
@include('paroisses.components.header')

<!-- Structure principale de la page -->
<div class="layout">
    <!-- Sidebar visible sur grand écran -->
    <div class="sidebar-app">
        @include('paroisses.components.sidebar')
    </div>

    <!-- Navbar visible sur petit écran -->
    <div class="navbar-app">
        @include('paroisses.components.navbars')
    </div>

    <!-- Contenu principal -->
    <div class="main-content">
        @yield('content') <!-- Contenu injecté par les vues enfants -->
    </div>
</div>

<!-- Inclusion du pied de page -->
@include('paroisses.components.footer')

<!-- Script Alpine.js pour les composants interactifs -->
<script src="https://unpkg.com/alpinejs@3.14.9/dist/cdn.min.js"></script>

<!-- Script principal compilé avec Vite -->
@vite('resources/js/app.js')
</body>
</html>
