<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Paroisses;
use App\Models\DemandeCeremonie;

class DemandeCeremoniePolicies
{
    public function create(User $user, Paroisses $paroisse): bool
    {
        // Ex: seuls admin + entreprise_funeraire liées à cette paroisse
        if ($user->role === 'admin') return true;

        if ($user->role === 'entreprise_funeraire') {
            // règle selon ton schéma (ex. relation user->entreprise->paroisses)
            return $user->entreprise?->paroisses()->whereKey($paroisse->id)->exists();
        }

        return false;
    }
}
