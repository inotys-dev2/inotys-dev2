<?php

namespace App\Http\Controllers\Entreprise;

use App\Http\Controllers\Controller;
use App\Models\DemandeCeremonie;
use App\Models\Entreprises;
use App\Models\Paroisses;
use App\Models\UtilisateurParoisse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EntrepriseAgendaController extends Controller
{

    public function agenda($uuid) {
        $entreprise = Entreprises::where('uuid', $uuid)->firstOrFail();
        $paroisses = Paroisses::all();
        $ceremonies = DemandeCeremonie::with([
            'users_paroisses.user',   // pour officiant->user->prenom/nom
            'paroisse',         // pour paroisse->nom/adresse
            'createur'           // pour creator->prenom/nom
        ])->where('entreprise_id', $entreprise->id)->get();

        $events = $ceremonies->map(function (DemandeCeremonie $c) {
            // reconstitution de la date/heure
            $heureNorm   = Carbon::parse($c->heure_ceremonie)->format('H:i:s');
            $dateHeure   = $c->date_ceremonie->format('Y-m-d') . ' ' . $heureNorm;
            $startCarbon = Carbon::createFromFormat('Y-m-d H:i:s', $dateHeure);

            // propriétés communes
            $extended = [
                'nomContactFamille'  => $c->nom_contact_famille,
                'telContactFamille'  => $c->telephone_contact_famille,
                'paroisseId'         => $c->paroisse->id,
                'paroisseNom'        => $c->paroisse->name,
                'paroissePhone'      => $c->paroisse->phone,
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
        return view('entreprise.agenda.view', compact('entreprise','paroisses', 'events'));
    }

    public function getWorkingDays(Request $request) {
        $validated = $request->validate([
            'paroisseId' => ['required', 'integer', 'exists:paroisses,id'],
        ]);
        $paroisse = Paroisses::findOrFail($validated['paroisseId']);

        $slots = $paroisse->availabilitySlots()->select('day_of_week', 'start_time', 'end_time')->get();
        $businessHours = $slots->flatMap(function($slot) {
            $jours = $slot->day_of_week !== null
                ? [(int) $slot->day_of_week]
                : range(0, 6);

            // Pour chaque jour, on génère une entrée séparée
            return collect($jours)->map(fn($d) => [
                'startTime'  => Carbon::parse($slot->start_time)->format('H:i'),
                'endTime'    => Carbon::parse($slot->end_time)->format('H:i'),
            ]);
        })->values();
        $businessDays = $paroisse->availabilitySlots()->select('day_of_week')->get();

        return response()->json([
            'businessHours' => $businessHours,
            'businessDays'  => $businessDays,
        ]);
    }

    public function envoyer(Request $request, $uuid)
    {
        $entreprise = Entreprises::where('uuid', $uuid)->firstOrFail();

        $data = $request->validate([
            'paroisses_id'              => 'required|exists:paroisses,id',
            'nom_defunt'                => 'required|string',
            'date_ceremonie'            => 'required|date',
            'heure_ceremonie'           => 'required',
            'duree_minutes'             => 'nullable|integer',
            'nom_contact_famille'       => 'nullable|string',
            'telephone_contact_famille' => [
                'nullable',
                'regex:/^\+?[0-9\s\-]{6,20}$/'
            ],
            'demandes_speciales'        => 'nullable|string',
        ]);

        // Gestion création vs mise à jour
        if ($request->filled('id')) {
            $demande = DemandeCeremonie::findOrFail($request->query('id'));

            // Ajout du champ modifié par
            $data['modifie_par'] = auth()->id();

            $demande->update($data);
            $message = 'Votre demande a bien été mise à jour.';
        } else {
            // Création : on complète les champs manquants
            $data['entreprise_id']   = $entreprise->id;
            $data['cree_par']        = auth()->id();
            $data['statut']          = 'en_attente';
            $data['statut_paiement'] = 'en_attente';
            $data['modifie_par']     = null; // par défaut

            DemandeCeremonie::create($data);
            $message = 'Votre demande a bien été envoyée.';
        }

        return redirect()
            ->route('entreprise.agenda.view', ['uuid' => $uuid])
            ->with('success', $message);
    }

    public function showAllDemande(Request $request, $uuid)
    {
        // 1. Récupère l'entreprise
        $entreprise = Entreprises::where('uuid', $uuid)->firstOrFail();

        // 2. Prépare la requête de base
        $query = DemandeCeremonie::where('entreprise_id', $entreprise->id);

        if ($request->filled('paroisses')) {
            $query->where('paroisses_id', $request->input('paroisses'));
        }
        // 3. Récupère toutes les demandes
        $toutesDemandes = $query->get();

        // 4. Sépare en deux collections selon le statut
        $demandesConfirmees = $toutesDemandes
            ->filter(fn($d) => $d->statut === 'acceptee');

        $demandesEnAttente = $toutesDemandes
            ->filter(fn($d) => $d->statut === 'en_attente');

        $demandesPasseeOuAnnulee = $toutesDemandes
            ->filter(fn($d) => $d->statut === 'passee' || $d->statut === 'refusee');

        // 5. Charge la liste des paroisses pour le select
        $paroisses = Paroisses::all();

        // 6. Passe tout à la vue
        return view('entreprise.agenda.demandes', compact(
            'entreprise',
            'demandesConfirmees',
            'demandesEnAttente',
            'demandesPasseeOuAnnulee',
            'paroisses',
            'uuid'
        ));
    }
    public function detailDemande($id)
    {
        $demande = DemandeCeremonie::where('id', $id)->firstOrFail();
        return view('entreprise.agenda.components.demandes.details', compact('demande'));
    }

    public function showForm(Request $request, $uuid)
    {
        $entreprise = Entreprises::where('uuid', $uuid)->firstOrFail();
        $paroisses  = Paroisses::all();                                 // TOUJOURS charger toutes les paroisses
        $officiants = UtilisateurParoisse::with('user')->get();                   // TOUJOURS charger tous les officiants

        $demande = null;
        if ($request->filled('id')) {
            // Mode édition : charger la demande existante
            $demande = DemandeCeremonie::findOrFail($request->query('id'));
        }

        // Déterminer les valeurs par défaut du formulaire
        if ($demande) {
            $defaultDate     = $demande->date_ceremonie->format('Y-m-d');
            $defaultTime     = $demande->heure_ceremonie->format('H:i');
            $defaultDuration = $demande->duree_minutes;

        } elseif ($request->filled('start') && $request->filled('end')) {
            $dtStart = Carbon::parse(str_replace(' ', '+', $request->query('start')));
            $dtEnd   = Carbon::parse(str_replace(' ', '+', $request->query('end')));
            $defaultDate     = $dtStart->toDateString();
            $defaultTime     = $dtStart->format('H:i');
            $defaultDuration = $dtStart->diffInMinutes($dtEnd);
        } else {
            $defaultDate     = now()->format('Y-m-d');
            $defaultTime     = now()->format('H:i');
            $defaultDuration = 60;
        }

        return view('entreprise.agenda.demande', compact(
            'entreprise',
            'paroisses',
            'officiants',
            'demande',
            'defaultDate',
            'defaultTime',
            'defaultDuration'
        ));
    }
}
