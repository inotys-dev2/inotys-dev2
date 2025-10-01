<?php

// app/Http/Middleware/IdentifyNetwork.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Config;
use App\Models\Network;
use Illuminate\Support\Facades\DB;

class IdentifyNetwork
{
    public function handle($request, Closure $next)
    {
        // 1. Récupération du tenant via sous-domaine
        $subdomain = explode('.', $request->getHost())[0];
        $network   = Network::where('slug', $subdomain)->firstOrFail();

        // 2. Configuration de la connexion 'tenant'
        Config::set('database.connections.tenant', array_merge(
            config('database.tenant_template'),
            ['database' => 'tenant_' . $network->id]
        ));
        Config::set('database.default', 'tenant');

        // 3. Reconnexion
        DB::purge('tenant');
        DB::reconnect('tenant');

        // 4. Bindez L’INSTANCE SUR LA CLASSE Network
        app()->instance(Network::class, $network);

        return $next($request);
    }
}
