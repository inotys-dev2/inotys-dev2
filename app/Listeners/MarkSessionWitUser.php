<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class MarkSessionWithUser
{
    public function handle(Login $event)
    {
        // on récupère l’ID de la session actuelle
        $sessionId = Session::getId();

        // et on met à jour la colonne user_id dans la table sessions
        DB::table('sessions')
            ->where('id', $sessionId)
            ->update(['user_id' => $event->user->id]);
    }
}
