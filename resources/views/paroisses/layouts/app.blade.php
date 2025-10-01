<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="UTF-8">
        <title>Dashboard</title>
        <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

        <meta name="csrf-token" content="{{ csrf_token() }}">
        @vite('resources/css/app.css')

        <!-- Scripts -->
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
    <style>
        .sidebar-app { display: block; }
        .navbar-app  { display: none;  }


        @media (max-width: 1150px) {
            .sidebar-app {
                display: none;
            }
            .navbar-app  { display: block;}
        }

    </style>
    <body>

    @include('paroisses.components.header')

    <div class="layout">
        <div class="sidebar-app">
            @include('paroisses.components.sidebar')
        </div>
        <div class="navbar-app">
            @include('paroisses.components.navbars')
        </div>
        <div class="main-content">
            @yield('content')
        </div>
    </div>

    @include('paroisses.components.footer')

    <script src="https://unpkg.com/alpinejs@3.14.9/dist/cdn.min.js"></script>
    @vite('resources/js/app.js')
    </body>
</html>
