<?php

namespace App\Http\Controllers\Entreprise;

use App\Http\Controllers\Controller;
use App\Models\Entreprises;

class EntrepriseController extends Controller
{
    public function dashboard($uuid)
    {
        $entreprise = Entreprises::where('uuid', $uuid)->firstOrFail();
        $user = auth()->user();

        if (!auth()->user()->users_entreprises()->where('entreprise_id', $entreprise->id)->exists()) {
            abort(403, 'Accès refusé');
        }

        return view('entreprise.dashboard', ['entreprise' => $entreprise, 'user' => $user]);
    }
}
