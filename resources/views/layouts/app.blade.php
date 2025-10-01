<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ config('app.name') }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-dTfgeiQFWWLEkPvje6La6ZaJCMXf0CPxW20fAe7z3IOYhVbK+Jmq8NCLGTQezQ0KFV0n47mSvF6U1Peu4gf1NQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    @vite('resources/css/app.css')

    @php
        $theme = auth()->user()->theme;
        $paths = [
          "resources/css/{$theme}/main.css",
        ];
    @endphp

    @foreach ($paths as $path)
        @if(file_exists(resource_path('css/'. $theme .'/'. basename($path))))
            @vite($path)
        @endif
    @endforeach

</head>
<body class="font-sans antialiased bg-gray-100">
<div class="min-h-screen">
    {{-- En-tête (si défini) --}}
    @isset($header)
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
    @endisset

    {{-- Contenu principal --}}
    <main class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{ $slot }}
        </div>
    </main>
</div>
</body>
</html>
