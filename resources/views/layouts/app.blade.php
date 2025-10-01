<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    {{-- Le titre de la page correspond au nom de l'application défini dans config/app.php --}}
    <title>{{ config('app.name') }}</title>

    {{-- Import de la librairie Font Awesome (icônes) via CDN --}}
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
          integrity="sha512-dTfgeiQFWWLEkPvje6La6ZaJCMXf0CPxW20fAe7z3IOYhVbK+Jmq8NCLGTQezQ0KFV0n47mSvF6U1Peu4gf1NQ=="
          crossorigin="anonymous"
          referrerpolicy="no-referrer" />

    {{-- Chargement du fichier CSS principal compilé par Vite --}}
    @vite('resources/css/app.css')

    @php
        // On récupère le thème défini pour l'utilisateur connecté
        $theme = auth()->user()->theme;

        // Tableau des fichiers CSS à inclure selon le thème
        $paths = [
            "resources/css/{$theme}/main.css",
        ];
    @endphp

    {{-- Pour chaque fichier CSS du thème, on vérifie s'il existe avant de l'inclure --}}
    @foreach ($paths as $path)
        @if(file_exists(resource_path('css/'. $theme .'/'. basename($path))))
            @vite($path)
        @endif
    @endforeach
</head>

<body class="font-sans antialiased bg-gray-100">
<div class="min-h-screen">
    {{-- Section en-tête : affichée uniquement si la variable $header est définie --}}
    @isset($header)
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
    @endisset

    {{-- Contenu principal de la page --}}
    <main class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{ $slot }}
        </div>
    </main>
</div>
</body>
</html>
