<?php

namespace App\Http\Controllers\Paroisses;

use App\Models\Paroisses;

class ParoissesPaiementController
{
    public function index($uuid) {

        $paroisse = Paroisses::where('uuid', $uuid)->firstOrFail();
        return view('paroisses.paiements', compact('paroisse'));
    }
}
