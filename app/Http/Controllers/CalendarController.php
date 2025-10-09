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
    public function index(Request $request, string $uuid)
    {
        $scope = $request->route('scope'); // injecté par defaults()
        $Entreprises = Entreprises::all();
        $Paroisses = Paroisses::all();

        switch ($scope) {
            case 'entreprise':
                $entreprise = Entreprises::where('uuid', $uuid)->firstOrFail();
                return view('entreprises.calendar');

            case 'paroisse':
                $paroisse = Paroisses::where('uuid', $uuid)->firstOrFail();
                return view('paroisses.agenda.calendar', ['paroisse' => $paroisse, 'entreprises' => $Entreprises]);

        }
        return redirect('/dashboard');
    }

    public function events(Request $request, string $uuid)
    {
        $request->validate([
            'from'    => 'required|date',
            'to'      => 'required|date|after:from',
            'tags'    => 'nullable|array',
            'affichage' => 'nullable|in:toutes,assignees,non_assignees',
        ]);

        $scope = $request->route('scope');
        [$entrepriseId, $paroisseId] = $this->resolveTenantIds($scope, $uuid);

        $from = Carbon::parse($request->get('from'));
        $to   = Carbon::parse($request->get('to'));

        $q = DemandeCeremonie::query()
            ->whereBetween('ceremony_date', [$from->toDateString(), $to->toDateString()]);

        // Isolation par tenant
        if ($scope === 'entreprise') {
            $q->where('entreprise_id', $entrepriseId);
        } else { // paroisse
            $q->where('paroisse_id', $paroisseId);
        }

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

    public function store(Request $request, string $uuid)
    {
        $request->validate([
            'title'    => 'required|string|max:255',
            'start_at' => 'required|date',
            'end_at'   => 'required|date|after:start_at',
            'status'   => 'required|in:treatment,waiting,accepted,canceled,passed',
        ]);

        $scope = $request->route('scope', 'entreprise');
        [$entrepriseId, $paroisseId] = $this->resolveTenantIds($scope, $uuid);

        $start = Carbon::parse($request->input('start_at'));
        $end   = Carbon::parse($request->input('end_at'));

        $row = DemandeCeremonie::create([
            'entreprise_id' => $scope === 'entreprise' ? $entrepriseId : null,
            'paroisse_id'   => $scope === 'paroisse'   ? $paroisseId   : null,
            'deceased_name' => $request->input('title'),
            'ceremony_date' => $start->toDateString(),
            'ceremony_hour' => $start->format('H:i:s'),
            'duration_time' => $start->diffInMinutes($end),
            'statut'        => $request->input('status'),
            // 'assigned_at' => $request->user()->id, // si tu veux auto-assigner le créateur
        ]);

        return response()->json(['message' => 'Créé', 'id' => $row->id], 201);
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

    public function destroy(Request $request, string $uuid, DemandeCeremonie $ceremony)
    {
        $this->extracted($request, $uuid, $ceremony);

        $ceremony->delete();
        return response()->json(['message' => 'Supprimé']);
    }

    /**
     * Résout l’ID interne depuis l’UUID et le scope.
     * Tables d’après ta migration: `entreprise` et `paroisse` (singulier).
     */
    private function resolveTenantIds(string $scope, string $uuid): array
    {
        $entrepriseId = null;
        $paroisseId   = null;

        if ($scope === 'entreprise') {
            $entrepriseId = (int) DB::table('entreprise')->where('uuid', $uuid)->value('id');
            abort_unless($entrepriseId, 404);
        } else { // paroisse
            $paroisseId = (int) DB::table('paroisse')->where('uuid', $uuid)->value('id');
            abort_unless($paroisseId, 404);
        }

        return [$entrepriseId, $paroisseId];
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
}
