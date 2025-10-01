<?php

// app/Http/Middleware/EnsureSessionHasUser.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class EnsureSessionHasUser
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            DB::table('sessions')
                ->where('id', Session::getId())
                ->update(['user_id' => Auth::id()]);
        }

        return $next($request);
    }
}
