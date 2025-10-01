<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard {{ $entreprise->name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite('resources/css/app.css')

    @php
        $theme = auth()->user()->theme;
        $type = auth()->user()->access;
        $paths = [
          "resources/css/{$theme}/{$type}/main.css",
        ];
    @endphp

    @foreach ($paths as $path)
        @if(file_exists(resource_path('css/'. $theme .'/'. $type .'/'. basename($path))))
            @vite($path)
        @endif
    @endforeach
</head>
<body>

@include('entreprise.components.header')

<div class="layout">
    @include('entreprise.components.sidebar')
    <div class="main-content">
        @yield('content')
    </div>
</div>

@include('entreprise.components.footer')

<script src="https://unpkg.com/alpinejs@3.14.9/dist/cdn.min.js"></script>
@vite('resources/js/app.js')
</body>
</html>
