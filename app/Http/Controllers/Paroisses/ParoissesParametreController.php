<?php

namespace App\Http\Controllers\Paroisses;

use App\Models\Paroisses;

class ParoissesParametreController
{
    public function index($uuid) {

        $paroisse = Paroisses::where('uuid', $uuid)->firstOrFail();
        return view('paroisses.parametre', compact('paroisse'));
    }
}
