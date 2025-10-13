<?php

namespace App\Http\Controllers;

use App\Models\DemandeCeremonie;
use App\Models\Entreprises;
use App\Models\Paroisses;
use Carbon\Carbon;
use Illuminate\Http\Request;
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
        $entrprise = Entreprises::where('uuid', $uuid)->firstOrFail();
        $Paroisses = Paroisses::all();
        return view('entreprise.agenda.calendar', ['paroisses' => $Paroisses, 'entreprise' => $entrprise]);
    }

    public function events(Request $request)
    {
        $data = $request->validate([
            'from'          => 'required|date',
            'to'            => 'required|date|after:from',
            'paroisse_id'   => 'nullable|integer|exists:paroisse,id',
            'entreprise_id' => 'nullable|integer|exists:entreprises,id',
            'tags'          => 'nullable|array',
            'affichage'     => 'nullable|in:toutes,assignees,non_assignees',
        ]);

        $from = Carbon::parse($data['from'])->startOfDay();
        $to   = Carbon::parse($data['to'])->endOfDay();

        $allowed = ['treatment','waiting','accepted','canceled','passed'];
        $tags = array_values(array_intersect($data['tags'] ?? [], $allowed));

        $q = DemandeCeremonie::query()
            ->with('entreprise')
            ->whereBetween('ceremony_date', [$from->toDateString(), $to->toDateString()])
            ->when(!empty($data['paroisse_id'] ?? null), fn($q) =>
            $q->where('paroisse_id', $data['paroisse_id'])
            )
            ->when(!empty($data['entreprise_id'] ?? null), fn($q) =>
            $q->where('entreprise_id', $data['entreprise_id'])
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
                'title'  => $c->deceased_name ?? 'Cérémonie',
                'status' => $c->statut,
                'start'  => $start->toIso8601String(),
                'end'    => $end->toIso8601String(),
                'score'  => $c->score,
                'cancel_reason' => $c->cancel_reason,
                'special_request' => $c->special_request,
                'contact_family_name' => $c->contact_family_name,
                'contact_family_phone' => $c->telephone_contact_family,
                'pompe_funebre' => $c->entreprise,
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
        $data = $request->validate([
            'title'    => 'sometimes|required|string|max:255',
            'start_at' => 'sometimes|required|date',
            'end_at'   => 'sometimes|required|date|after:start_at',
            'status'   => 'sometimes|required|in:treatment,waiting,accepted,canceled,passed',
        ]);

        // Vérrouillage tenant : on empêche la MAJ si l’élément n’appartient pas au tenant de l’URL
        $this->extracted($request, $uuid, $ceremony);

        if (isset($data['start_at'], $data['end_at'])) {
            $start = Carbon::parse($data['start_at']);
            $end   = Carbon::parse($data['end_at']);
            $ceremony->ceremony_date = $start->toDateString();
            $ceremony->ceremony_hour = $start->format('H:i:s');
            $ceremony->duration_time = $start->diffInMinutes($end);
            unset($data['start_at'], $data['end_at']);
        }
        if (isset($data['title'])) {
            $ceremony->deceased_name = $data['title'];
            unset($data['title']);
        }
        if (isset($data['status'])) {
            $ceremony->statut = $data['status'];
            unset($data['status']);
        }

        $ceremony->fill($data)->save();
        return response()->json(['message' => 'Mis à jour']);
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
