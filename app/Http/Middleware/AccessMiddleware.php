<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AccessMiddleware
{

    public function handle($request, Closure $next, ...$access)
    {
        if (!Auth::check()) {
            return redirect()->route('welcome');
        }

        $user = Auth::user();

        if (!in_array($user->access, $access)) {
            abort(403, 'Accès non autorisé.');
        }

        return $next($request);
    }
}

