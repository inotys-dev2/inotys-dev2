<?php

namespace App\Http\Controllers\Entreprise;

use App\Http\Controllers\Controller;
use App\Models\DemandeCeremonie;
use App\Models\Entreprises;
use App\Models\Paroisses;
use App\Models\UtilisateurParoisse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EntrepriseAgendaController extends Controller
{
    public function agenda($uuid)
    {
        $entreprise = Entreprises::where('uuid', $uuid)->firstOrFail();
        $parishes = Paroisses::all();

        $ceremonies = DemandeCeremonie::with([
            'userParoisse.user',
            'paroisse',
            'userEntreprise'
        ])->where('entreprise_id', $entreprise->id)->get();

        $events = $ceremonies->map(function (DemandeCeremonie $ceremony) {
            $hourNormalized = Carbon::parse($ceremony->ceremony_hour)->format('H:i:s');
            $datetime = $ceremony->ceremony_date->format('Y-m-d') . ' ' . $hourNormalized;
            $start = Carbon::createFromFormat('Y-m-d H:i:s', $datetime);

            $extended = [
                'familyContactName'  => $ceremony->contact_family_name,
                'familyContactPhone' => $ceremony->telephone_contact_family,
                'parishId'           => $ceremony->paroisse->id,
                'parishName'         => $ceremony->paroisse->name,
                'parishPhone'        => $ceremony->paroisse->phone,
                'specialRequests'    => $ceremony->special_requests,
                'amount'             => $ceremony->sum,
                'paymentStatus'      => $ceremony->statut_paiement,
                'createdByFirstName' => $ceremony->userEntreprise->prenom,
                'createdByLastName'  => $ceremony->userEntreprise->nom,
                'created_at'         => $ceremony->created_at->toDateTimeString(),
                'updated_at'         => $ceremony->updated_at->toDateTimeString(),
            ];

            if ($ceremony->userParoisse && $ceremony->userParoisse->user) {
                $extended['officiantFirstName'] = $ceremony->userParoisse->user->prenom;
                $extended['officiantLastName']  = $ceremony->userParoisse->user->nom;
            }

            return [
                'id'            => $ceremony->id,
                'title'         => $ceremony->deceased_name . ($ceremony->statut ? " ({$ceremony->statut})" : ''),
                'start'         => $start->toIso8601String(),
                'end'           => $start->copy()->addMinutes($ceremony->duration_time)->toIso8601String(),
                'extendedProps' => $extended,
            ];
        });

        return view('entreprise.agenda.view', compact('entreprise', 'parishes', 'events'));
    }

    public function getWorkingDays(Request $request)
    {
        $validated = $request->validate([
            'parishId' => ['required', 'integer', 'exists:paroisses,id'],
        ]);

        $parish = Paroisses::findOrFail($validated['parishId']);

        $slots = $parish->availabilitySlots()->select('day_of_week', 'start_time', 'end_time')->get();

        $businessHours = $slots->flatMap(function ($slot) {
            $days = $slot->day_of_week !== null ? [(int) $slot->day_of_week] : range(0, 6);

            return collect($days)->map(fn($d) => [
                'startTime' => Carbon::parse($slot->start_time)->format('H:i'),
                'endTime'   => Carbon::parse($slot->end_time)->format('H:i'),
            ]);
        })->values();

        $businessDays = $parish->availabilitySlots()->select('day_of_week')->get();

        return response()->json([
            'businessHours' => $businessHours,
            'businessDays'  => $businessDays,
        ]);
    }

    public function envoyer(Request $request, $uuid)
    {
        $company = Entreprises::where('uuid', $uuid)->firstOrFail();

        $data = $request->validate([
            'paroisse_id'              => 'required|exists:paroisses,id',
            'deceased_name'            => 'required|string',
            'ceremony_date'            => 'required|date',
            'ceremony_hour'            => 'required',
            'duration_time'            => 'nullable|integer',
            'contact_family_name'      => 'nullable|string',
            'telephone_contact_family' => ['nullable', 'regex:/^\+?[0-9\s\-]{6,20}$/'],
            'special_requests'         => 'nullable|string',
        ]);

        if ($request->filled('id')) {
            $ceremony = DemandeCeremonie::findOrFail($request->query('id'));
            $ceremony->update($data);
            $message = 'The request has been updated successfully.';
        } else {
            $data['entreprise_id']      = $company->id;
            $data['user_entreprise_id'] = auth()->id();
            $data['statut']             = 'waiting';
            $data['statut_paiement']    = 'define';
            $data['duration_time']      = $data['duration_time'] ?? 60;

            DemandeCeremonie::create($data);
            $message = 'The request has been created successfully.';
        }

        return redirect()
            ->route('entreprise.agenda.view', ['uuid' => $uuid])
            ->with('success', $message);
    }

    public function showAllRequests($uuid)
    {
        $entreprise = Entreprises::where('uuid', $uuid)->firstOrFail();

        $statusOrder = ['treatment', 'waiting', 'accepted', 'canceled', 'passed'];
        $requests = DemandeCeremonie::where('entreprise_id', $entreprise->id)
            ->orderByRaw("FIELD(statut, '".implode("','", $statusOrder)."')")
            ->get();

        $counts = DemandeCeremonie::where('entreprise_id', $entreprise->id)
            ->select('paroisse_id', DB::raw('COUNT(*) AS total'))
            ->groupBy('paroisse_id')
            ->pluck('total', 'paroisse_id');

        $parishes = Paroisses::all();

        return view('entreprise.agenda.demandes', compact('entreprise', 'requests', 'parishes', 'counts'));
    }

    public function showRequestDetail($id)
    {
        $requestItem = DemandeCeremonie::where('id', $id)->firstOrFail();
        return view('entreprise.agenda.components.demandes.details', compact('requestItem'));
    }

    public function showForm(Request $request, $uuid)
    {
        $entreprise = Entreprises::where('uuid', $uuid)->firstOrFail();
        $parishes = Paroisses::all();
        $officiants = UtilisateurParoisse::with('user')->get();

        $ceremony = null;
        if ($request->filled('id')) {
            $ceremony = DemandeCeremonie::findOrFail($request->query('id'));
        }

        if ($ceremony) {
            $defaultDate     = $ceremony->ceremony_date->format('Y-m-d');
            $defaultTime     = $ceremony->ceremony_hour;
            $defaultDuration = $ceremony->duration_time;
        } elseif ($request->filled('start') && $request->filled('end')) {
            $start = Carbon::parse(str_replace(' ', '+', $request->query('start')));
            $end   = Carbon::parse(str_replace(' ', '+', $request->query('end')));
            $defaultDate     = $start->toDateString();
            $defaultTime     = $start->format('H:i');
            $defaultDuration = $start->diffInMinutes($end);
        } else {
            $defaultDate     = now()->format('Y-m-d');
            $defaultTime     = now()->format('H:i');
            $defaultDuration = 60;
        }

        return view('entreprise.agenda.demande', compact(
            'entreprise',
            'parishes',
            'officiants',
            'ceremony',
            'defaultDate',
            'defaultTime',
            'defaultDuration'
        ));
    }
}
