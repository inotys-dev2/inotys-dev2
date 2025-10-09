<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UtilisateurEntreprise;
use App\Models\UtilisateurPermission;
use App\Models\Entreprises;
use Carbon\Carbon;

class UtilisateurEntrepriseSeeder extends Seeder
{
    public function run(): void
    {
        /*$entreprise = Entreprises::first() ?? Entreprises::factory()->create();

        // Création d’un responsable
        $userResp = User::factory()->create();

        UtilisateurEntreprise::factory()
            ->for($userResp, 'user') // ou 'users' selon relation
            ->for($entreprise, 'entreprise')
            ->directeur()
            ->create();

        UtilisateurPermission::factory()
            ->for($userResp, 'user')
            ->for($entreprise, 'entreprise')
            ->responsable()
            ->create();

        // Création d’un membre
        $userMembre = User::factory()->create();

        UtilisateurEntreprise::factory()
            ->for($userMembre, 'user')
            ->for($entreprise, 'entreprise')
            ->create();

        UtilisateurPermission::factory()
            ->for($userMembre, 'user')
            ->for($entreprise, 'entreprise')
            ->create();*/
    }
}
