<?php

namespace App\Http\Controllers\Entreprise;

use App\Http\Controllers\Controller;
use App\Models\Entreprises;
use Illuminate\Http\Request;

class EntreprisePaiementController extends Controller
{
    public function creationDevis($uuid)
    {
        $entreprise = Entreprises::where('uuid', $uuid)->firstOrFail();

        return view('entreprise.paiement.creation_devis', compact('entreprise'));
    }

    public function attentes($uuid)
    {
        $entreprise = Entreprises::where('uuid', $uuid)->firstOrFail();

        return view('entreprise.paiement.attentes', compact('entreprise'));
    }

    public function effectues($uuid)
    {
        $entreprise = Entreprises::where('uuid', $uuid)->firstOrFail();

        return view('entreprise.paiement.effectues', compact('entreprise'));
    }

    public function historique($uuid)
    {
        $entreprise = Entreprises::where('uuid', $uuid)->firstOrFail();

        return view('entreprise.paiement.historique', compact('entreprise'));
    }

}
