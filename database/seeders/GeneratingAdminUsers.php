<?php

namespace Database\Seeders;

use App\Models\Entreprises;
use App\Models\Paroisses;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use App\Models\User;

class GeneratingAdminUsers extends Seeder
{
    public function run(): void
    {
        /*$now = Carbon::now();

        // Créer une entreprise
        $entreprise = Entreprises::factory()->create();

        // Créer une paroisse
        $paroisse = Paroisses::factory()->create();

        // Utilisateur avec accès "entreprise"
        $entrepriseUser = User::factory()->create([
            'prenom'     => 'Entreprise',
            'nom'        => 'System',
            'email'      => 'entreprise.system@obsek.fr',
            'profileImg' => 'admin.png',
            'password'   => bcrypt('admin'),
            'telephone'  => '0123456789',
            'role'       => 'Directeur',
            'access'     => 'entreprise',
            'last_seen'  => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $entrepriseUser->entreprises()->attach($entreprise->id, ['rank' => 'employer']);

        // Utilisateur avec accès "paroisses"
        $paroisseUser = User::factory()->create([
            'prenom'     => 'Paroisses',
            'nom'        => 'System',
            'email'      => 'paroisses.system@obsek.fr',
            'profileImg' => 'admin.png',
            'password'   => bcrypt('admin'),
            'telephone'  => '0123456789',
            'role'       => 'Directeur',
            'access'     => 'paroisses',
            'last_seen'  => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $paroisseUser->paroisses()->attach($paroisse->id, ['rank' => 'benevole']);

        // Utilisateur avec accès "admin"
        User::factory()->create([
            'prenom'     => 'Admin',
            'nom'        => 'System',
            'email'      => 'admin.system@obsek.fr',
            'profileImg' => 'admin.png',
            'password'   => bcrypt('admin'),
            'telephone'  => '0123456789',
            'role'       => 'Administrateur Obsek',
            'access'     => 'admin',
            'last_seen'  => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);*/
    }
}
