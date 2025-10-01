<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class UpdateLastSeen
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            // on stocke l’horodatage dans la session PHP
            session(['last_seen' => now()->toDateTimeString()]);
        }

        return $next($request);
    }
}

