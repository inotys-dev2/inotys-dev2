<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    {{-- Définition du jeu de caractères --}}
    <meta charset="utf-8">

    {{-- Permet à la page d’être responsive (s’adapte aux mobiles) --}}
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Jeton CSRF pour sécuriser les formulaires (obligatoire pour les requêtes POST) --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Titre de la page basé sur le nom de l’application --}}
    <title>{{ config('app.name') }}</title>

    {{-- Préchargement des polices pour de meilleures performances --}}
    <link rel="preconnect" href="https://fonts.bunny.net">

    {{-- Chargement de la police Figtree (400, 500, 600) --}}
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    {{-- Chargement de Font Awesome (icônes) depuis un CDN sécurisé --}}
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
        integrity="sha512-p9c6soZIb+Y6Vv5Bmy6R4yU1c+a8Z9+NWB60Kq+SVjZV+KGd4vE7qlXnY2TJDog6VoaZC+OrMZ6YI2x1Q+spkg=="
        crossorigin="anonymous"
        referrerpolicy="no-referrer"
    />

    {{-- Import du CSS principal compilé avec Vite --}}
    @vite('resources/css/app.css')

    @php
        // Définition du chemin CSS spécifique à la page de connexion
        $paths = [
            "resources/css/login/login.css",
        ];
    @endphp

    {{-- Vérifie si le fichier CSS existe avant de l’inclure --}}
    @foreach ($paths as $path)
        @if(file_exists(resource_path('css/login/' . basename($path))))
            @vite($path)
        @endif
    @endforeach
</head>

<body class="bg-memorys">
{{-- Conteneur principal de la page de login --}}
<div class="login">
    {{-- Boîte de connexion qui contiendra le formulaire --}}
    <div class="login-box">
        {{-- Le contenu est injecté via $slot (dans une vue enfant Blade) --}}
        {{ $slot }}
    </div>
</div>

{{-- Import du JavaScript principal compilé par Vite --}}
@vite('resources/js/app.js')
</body>
</html>
