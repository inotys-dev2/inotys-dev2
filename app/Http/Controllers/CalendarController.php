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

    public function events(Request $request, string $uuid)
    {
        $request->validate([
            'from'    => 'required|date',
            'to'      => 'required|date|after:from',
            'tags'    => 'nullable|array',
            'affichage' => 'nullable|in:toutes,assignees,non_assignees',
        ]);

        $from = Carbon::parse($request->get('from'));
        $to   = Carbon::parse($request->get('to'));

        $q = DemandeCeremonie::query()
            ->whereBetween('ceremony_date', [$from->toDateString(), $to->toDateString()]);

        // Assignation (FK users) via assigned_at
        $aff = $request->get('affichage', 'toutes');
        if ($aff === 'assignees')     $q->where('assigned_at', $request->user()->id);
        if ($aff === 'non_assignees') $q->whereNull('assigned_at');

        // Tags -> statut (ENUM exact)
        $allowed = ['treatment','waiting','accepted','canceled','passed'];
        $tags = collect((array)$request->get('tags', []))
            ->filter(fn($t) => in_array($t, $allowed, true))
            ->values()->all();

        if (!empty($tags)) {
            $q->whereIn('statut', $tags);
        }

        $rows = $q->orderBy('ceremony_date')->get();

        $events = $rows->map(function($c) use ($q) {
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
                'pompe_funebre' => Entreprises::where('id', $c->entreprise_id)->get()->first(),
            ];
        });

        return response()->json(['data' => $events]);
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

    /**
     * @param Request $request
     * @param string $uuid
     * @param DemandeCeremonie $ceremony
     * @return void
     */
    public function extracted(Request $request, string $uuid, DemandeCeremonie $ceremony): void
    {
        $scope = $request->route('scope', 'entreprise');
        [$entrepriseId, $paroisseId] = $this->resolveTenantIds($scope, $uuid);

        if ($scope === 'entreprise' && (int)$ceremony->entreprise_id !== (int)$entrepriseId) {
            abort(404);
        }
        if ($scope === 'paroisse' && (int)$ceremony->paroisse_id !== (int)$paroisseId) {
            abort(404);
        }
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
