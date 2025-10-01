<?php

namespace App\Http\Controllers\Entreprise;

use App\Http\Controllers\Controller;
use App\Models\Entreprises;
use App\Models\User;
use App\Models\UtilisateurEntreprise;

class EntrepriseAdminController extends Controller
{

    public function parameters($uuid)
    {
        $entreprise = Entreprises::where('uuid', $uuid)->firstOrFail();

        return view('entreprise.admin.parametre', compact('entreprise'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function profile($uuid)
    {
        $entreprise = Entreprises::where('uuid', $uuid)->firstOrFail();

        return view('entreprise.admin.profile', compact('entreprise'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function membres($uuid)
    {
        // Récupération de l’entreprise
        $entreprise = Entreprises::where('uuid', $uuid)->firstOrFail();

        // 1) On va chercher tous les user_id dans la table pivot
        $userIds = UtilisateurEntreprise::where('entreprise_id', $entreprise->id)
            ->pluck('users_id')      // renvoie une Collection d’IDs
            ->toArray();

        // 2) On récupère tous les utilisateurs en une seule requête
        $users = User::whereIn('id', $userIds)->get();

        return view('entreprise.admin.membres', compact('entreprise', 'users'));
    }


    /**
     * Display the specified resource.
     */
    public function logs($uuid)
    {
        $entreprise = Entreprises::where('uuid', $uuid)->firstOrFail();

        return view('entreprise.admin.logs', compact('entreprise'));
    }
}
