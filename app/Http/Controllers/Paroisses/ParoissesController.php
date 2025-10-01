<?php

namespace App\Http\Controllers\Paroisses;

use App\Http\Controllers\Controller;
use App\Models\Paroisses;

class ParoissesController extends Controller
{
    public function dashboard($uuid)
    {
        $paroisse = Paroisses::where('uuid', $uuid)->firstOrFail();
        $user = auth()->user();

        if (!auth()->user()->users_paroisses()->where('paroisses_id', $paroisse->id)->exists()) {
            abort(403, 'Accès refusé');
        }

        return view('paroisses.dashboard', ['paroisse' => $paroisse, 'user' => $user]);
    }
}
