<?php

namespace App\Http\Controllers\Paroisses;

use App\Models\DemandeCeremonie;
use App\Models\Entreprises;
use App\Models\Paroisses;
use Illuminate\Support\Facades\DB;


class ParoissesDemandesController
{

    public function index(string $uuid)
    {
        $paroisse = Paroisses::where('uuid', $uuid)->firstOrFail();

        $order = ['acceptee', 'en_attente', 'refusee', 'passee'];
        $demandes = DemandeCeremonie::where('paroisses_id', $paroisse->id)
            ->orderByRaw("FIELD(statut, '".implode("','", $order)."')")
            ->get();

        $counts = DemandeCeremonie::where('paroisses_id', $paroisse->id)
            ->select('entreprise_id', DB::raw('COUNT(*) AS total'))
            ->groupBy('entreprise_id')
            ->pluck('total', 'entreprise_id');

        $entreprises = Entreprises::all();

        return view('paroisses.agenda.demandes', compact(
            'paroisse',
            'demandes',
            'entreprises',
            'counts'
        ));
    }

}
