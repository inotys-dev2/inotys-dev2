<?php

namespace App\Http\Controllers\Paroisses;

use App\Http\Controllers\Controller;
use App\Models\DemandeCeremonie;
use App\Models\Entreprises;
use App\Models\Paroisses;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\URL;

class ParoissesAgendaController extends Controller {

    public function index($uuid)
    {
        $paroisse = Paroisses::where('uuid', $uuid)->firstOrFail();

        $entreprises = Entreprises::all();

        return view('paroisses.agenda.calendar', [
            'paroisse' => $paroisse,
            'entreprises' => $entreprises,
        ]);
    }
}
