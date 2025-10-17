<?php

namespace App\Http\Controllers;

use App\Models\DemandeCeremonie;
use App\Models\Entreprises;
use App\Models\Paroisses;
use App\Models\UtilisateurEntreprise;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CalendarController extends Controller
{
    public function indexParoisse(string $uuid)
    {
        $Entreprises = Entreprises::all();
        $paroisse = Paroisses::where('uuid', $uuid)->firstOrFail();
        return view('paroisses.agenda.calendar', ['paroisse' => $paroisse, 'entreprises' => $Entreprises]);
    }

    public function indexEntreprise(string $uuid)
    {
        $entreprise = Entreprises::where('uuid', $uuid)->firstOrFail();
        $Paroisses  = Paroisses::all();
        $counts     = DemandeCeremonie::where('entreprise_id', $entreprise->id)->select('paroisse_id', DB::raw('COUNT(*) AS total'))->groupBy('paroisse_id')->pluck('total', 'paroisse_id');
        $suivis = $entreprise->entreprise_user()
            ->orderBy('nom')
            ->get(['users.id','users.prenom', 'users.nom']);

        return view('entreprise.agenda.calendar', [
            'paroisses' => $Paroisses,
            'entreprise'=> $entreprise,
            'counts'    => $counts,
            'suivis'    => $suivis,
        ]);

    }

    public function events(Request $request)
    {
        $data = $request->validate([
            'from'          => 'required|date',
            'to'            => 'required|date|after:from',
            'paroisse_id'   => 'nullable|integer|exists:paroisse,id',
            'entreprise_id' => 'nullable|integer|exists:entreprise,id',
            'tags'          => 'nullable|array',
            'affichage'     => 'nullable|in:toutes,assignees,non_assignees',
        ]);

        $from = Carbon::parse($data['from'])->startOfDay();
        $to   = Carbon::parse($data['to'])->endOfDay();

        $allowed = ['treatment','waiting','accepted','canceled','passed'];
        $tags = array_values(array_intersect($data['tags'] ?? [], $allowed));

        $q = DemandeCeremonie::query()
            ->with('entreprise')
            ->with('paroisse')
            ->whereBetween('ceremony_date', [$from->toDateString(), $to->toDateString()])
            ->when(!empty($data['paroisse_id'] ?? null), fn($q) => $q->where('paroisse_id', $data['paroisse_id']))
            ->when(!empty($data['entreprise_id'] ?? null), fn($q) => $q->where('entreprise_id', $data['entreprise_id'])
            )
            ->when(($data['affichage'] ?? 'toutes') === 'assignees', fn($q) =>
            $q->where('assigned_at', $request->user()->id)
            )
            ->when(($data['affichage'] ?? 'toutes') === 'non_assignees', fn($q) =>
            $q->whereNull('assigned_at')
            )
            ->when(!empty($tags), fn($q) => $q->whereIn('statut', $tags))
            ->orderBy('ceremony_date');

        $rows = $q->get();

        $events = $rows->map(function ($c) {
            $date = Carbon::parse($c->ceremony_date);
            [$H,$M,$S] = array_pad(explode(':', $c->ceremony_hour ?: '00:00:00'), 3, '00');
            $start = $date->copy()->setTime((int)$H, (int)$M, (int)$S);
            $end   = $start->copy()->addMinutes(($c->duration_time ?? 60));

            return [
                'id'     => $c->id,
                'title'  => $c->deceased_name ?? 'Aucun défunt',
                'status' => $c->statut,
                'start'  => $start->toIso8601String(),
                'end'    => $end->toIso8601String(),
                'score'  => $c->score,
                'cancel_reason' => $c->cancel_reason,
                'special_request' => $c->special_request,
                'contact_family_name' => $c->contact_family_name,
                'contact_family_phone' => $c->telephone_contact_family,
                'pompe_funebre' => $c->entreprise,
                'paroisse' => $c->paroisse,
            ];
        });

        // Si une paroisse est sélectionnée → on ajoute les dispos dans le même JSON
        $availability = null;
        if (!empty($data['paroisse_id'])) {
            $availability = $this->getAvailabilityForParoisse((int)$data['paroisse_id']);
        }

        return response()->json([
            'data' => $events,
            'availability' => $availability,
        ]);
    }

    public function update(Request $request, string $uuid, DemandeCeremonie $ceremony)
    {

        return response()->json(['message' => 'Mis à jour']);
    }

    public function store(StoreDemandeCeremonieRequest $request)
    {
        $data = $request->validated();

        $paroisse = Paroisse::findOrFail($data['paroisse_id']);
        $this->authorize('create', [\App\Models\DemandeCeremonie::class, $paroisse]);

        // Récupère la disponibilité rattachée à la paroisse
        $availability = $paroisse->availability ?? ['days'=>[], 'start_time'=>null, 'end_time'=>null];

        // Règles métier supplémentaires
        $request->validate([
            'start'       => [new WithinAvailability($availability)],
            'end'         => [new WithinAvailability($availability)],
            'paroisse_id' => [new NoOverlap($data['paroisse_id'])],
        ]);

        return DB::transaction(function () use ($data) {
            // lock défensif contre la course
            $overlap = DemandeCeremonie::where('paroisse_id', $data['paroisse_id'])
                ->lockForUpdate()
                ->where(function($qq) use ($data) {
                    $qq->whereBetween('start', [$data['start'], $data['end']])
                        ->orWhereBetween('end',   [$data['start'], $data['end']])
                        ->orWhere(function($q2) use ($data){
                            $q2->where('start','<=',$data['start'])->where('end','>=',$data['end']);
                        });
                })
                ->exists();

            if ($overlap) {
                abort(422, 'Le créneau vient d’être pris. Merci de choisir une autre heure.');
            }

            $ceremony = DemandeCeremonie::create([
                'title'  => $data['title'],
                'paroisse_id' => $data['paroisse_id'],
                'start'  => $data['start'],
                'end'    => $data['end'],
                'special_request' => $data['special_request'] ?? null,
                'contact_family_name'  => $data['contact_family_name'] ?? null,
                'contact_family_phone' => $data['contact_family_phone'] ?? null,
                'status' => $data['status'] ?? 'confirmed',
                'created_by' => auth()->id(),
            ]);

            return response()->json(['success' => true, 'id' => $ceremony->id], 201);
        });
    }

    private function getAvailabilityForParoisse(int $paroisseId): ?array
    {
        $row = DB::table('availability_slots')->where('paroisse_id', $paroisseId)->first();
        if (!$row) return null;

        $days = $row->day_of_week;

        if (is_string($days)) {
            $decoded = json_decode($days, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $days = $decoded;
            } else {
                preg_match_all('/[1-7]/', $row->day_of_week, $m);
                $days = array_map('intval', $m[0] ?? []);
            }
        }

        $days = collect($days)->map(fn($d)=>(int)$d)->unique()->sort()->values()->all();

        return [
            'days'       => $days,                  // ex: [1,2,4,5,7]
            'start_time' => $row->start_time ?? null, // ex: '10:30:00'
            'end_time'   => $row->end_time   ?? null, // ex: '19:30:00'
        ];
    }

    public function availability(string $uuid)
    {
        $paroisse = Paroisses::where('uuid', $uuid)->firstOrFail();

        $cfg = $this->getAvailabilityForParoisse((int)$paroisse->id);
        if (!$cfg) {
            return response()->json(['days' => [], 'start_time' => null, 'end_time' => null]);
        }
        return response()->json($cfg);
    }
}
