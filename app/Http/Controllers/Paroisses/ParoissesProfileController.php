<?php

namespace App\Http\Controllers\Paroisses;

use App\Models\Paroisses;

class ParoissesProfileController
{
    public function index($uuid) {

        $paroisse = Paroisses::where('uuid', $uuid)->firstOrFail();
        return view('paroisses.profile', compact('paroisse'));
    }
}
