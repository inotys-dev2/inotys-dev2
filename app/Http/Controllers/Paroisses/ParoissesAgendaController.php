<?php

namespace App\Http\Controllers\Paroisses;

use App\Models\AvailabilitySlot;
use App\Models\DemandeCeremonie;
use App\Models\Paroisses;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ParoissesAgendaController
{
    public function agenda($uuid) {

        $paroisse = Paroisses::where('uuid', $uuid)->firstOrFail();

        $ceremonies = DemandeCeremonie::with([
            'users_paroisses.user',   // pour officiant->user->prenom/nom
            'entreprise',         // pour entreprise->nom/adresse
            'createur'           // pour creator->prenom/nom
        ])->where('paroisses_id', $paroisse->id)->get();

        $events = $ceremonies->map(function (DemandeCeremonie $c) {
            // reconstitution de la date/heure
            $heureNorm   = Carbon::parse($c->heure_ceremonie)->format('H:i:s');
            $dateHeure   = $c->date_ceremonie->format('Y-m-d') . ' ' . $heureNorm;
            $startCarbon = Carbon::createFromFormat('Y-m-d H:i:s', $dateHeure);

            // propriétés communes
            $extended = [
                'nomContactFamille'  => $c->nom_contact_famille,
                'telContactFamille'  => $c->telephone_contact_famille,
                'entrepriseId'       => $c->entreprise->id,
                'entrepriseNom'      => $c->entreprise->name,
                'entreprisePhone'    => $c->entreprise->phone,
                'demandesSpeciales'  => $c->demandes_speciales,
                'montant'            => $c->montant,
                'statutPaiement'     => $c->statut_paiement,
                'creeParPrenom'      => $c->createur->prenom,
                'creeParNom'         => $c->createur->nom,
                'created_at'         => $c->created_at->toDateTimeString(),
                'updated_at'         => $c->updated_at->toDateTimeString(),
            ];

            // on n'ajoute officiant que s'il existe
            if ($c->officiant && $c->officiant->user) {
                $extended['officiantPrenom'] = $c->officiant->user->prenom;
                $extended['officiantNom']    = $c->officiant->user->nom;
            }

            return [
                'id'            => $c->id,
                'title'         => $c->nom_defunt . ($c->statut ? " ({$c->statut})" : ''),
                'start'         => $startCarbon->toIso8601String(),
                'end'           => $startCarbon->copy()
                    ->addMinutes($c->duree_minutes)
                    ->toIso8601String(),
                'extendedProps' => $extended,
            ];
        });
        return view('paroisses.agenda.view', compact('paroisse', 'events'));
    }
}
