<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link
            rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
            integrity="sha512-p9c6soZIb+Y6Vv5Bmy6R4yU1c+a8Z9+NWB60Kq+SVjZV+KGd4vE7qlXnY2TJDog6VoaZC+OrMZ6YI2x1Q+spkg=="
            crossorigin="anonymous"
            referrerpolicy="no-referrer"
        />

        <!-- Scripts -->
        @vite('resources/css/app.css')

        @php
            $paths = [
              "resources/css/login/login.css",
            ];
        @endphp

        @foreach ($paths as $path)
            @if(file_exists(resource_path('css/login/'. basename($path))))
                @vite($path)
            @endif
        @endforeach
    </head>
    <body class="bg-memorys">
        <div class="login">
            <div class="login-box">
                {{ $slot }}
            </div>
        </div>
        @vite('resources/js/app.js')
    </body>
</html>
