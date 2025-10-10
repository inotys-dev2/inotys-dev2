<?php

namespace Database\Seeders;

use App\Models\AvailabilitySlot;
use App\Models\DemandeCeremonie;
use App\Models\UtilisateurEntreprise;
use App\Models\UtilisateurParoisse;
use App\Models\UtilisateurPermission;
use Database\Factories\EntreprisesFactory;
use Database\Factories\ParoissesFactory;
use Database\Factories\UserFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AutomatiqueSeeder extends Seeder
{
    public function run(): void
    {
        /**
         * ==========================
         *   ENTREPRISE FUNÉRAIRE
         * ==========================
         */

        $Memorys = EntreprisesFactory::new()->create([
            'name' => "Memorys",
            'email' => 'memorys@obsek.fr',
        ]);

        $DirecteurMemorys = UserFactory::new()->DirectionEntreprise()->create([
            'email' => 'memorys@obsek.fr',
            'password' => bcrypt('admin'),
        ]);
        UtilisateurEntreprise::factory()->for($DirecteurMemorys, 'user')->for($Memorys, 'entreprise')->create();
        UtilisateurPermission::factory()->for($DirecteurMemorys, 'user')->for($Memorys, 'entreprise')->admin()->create();

        $Diocese = ParoissesFactory::new()->create([
            'name' => 'Diocese de Blois',
            'email' => 'diocese@obsek.fr',
        ]);

        $DirecteurDiocese = UserFactory::new()->DirecteurParoisse()->create([
            'email' => 'diocese@obsek.fr',
            'password' => bcrypt('admin'),
        ]);

        $userparoises = UtilisateurParoisse::factory()->for($DirecteurDiocese, 'user')->for($Diocese, 'paroisse')->create();

        DB::table('availability_slots')->insert([
            'paroisse_id' => $Diocese->id,
            'day_of_week' => "[1,2,4,5,7]",
            'start_time'  => '10:30:00',
            'end_time'    => '19:30:00',
        ]);

        //------------------------------------------------

        $entreprise = EntreprisesFactory::new()->create();

        $DirectionEntrepriseUser   = UserFactory::new()->DirectionEntreprise()->create();
        $ResponsableEntrepriseUsers= UserFactory::new()->ResponsableEntreprise()->count(2)->create();
        $EmployerEntrepriseUsers   = UserFactory::new()->EmployerEntreprise()->count(5)->create();

        UtilisateurPermission::factory()->for($DirectionEntrepriseUser,'user')->for($entreprise,'entreprise')->admin()->create();
        UtilisateurEntreprise::factory()->for($DirectionEntrepriseUser,'user')->for($entreprise,'entreprise')->create();

        $ResponsableEntrepriseUsers->each(function ($user) use ($entreprise) {
            UtilisateurEntreprise::factory()->for($user,'user')->for($entreprise,'entreprise')->create();
            UtilisateurPermission::factory()->for($user,'user')->for($entreprise,'entreprise')->responsable()->create();
        });

        $EmployerEntrepriseUsers->each(function ($user) use ($entreprise) {
            UtilisateurEntreprise::factory()->for($user,'user')->for($entreprise,'entreprise')->create();
            UtilisateurPermission::factory()->for($user,'user')->for($entreprise,'entreprise')->create();
        });

        /**
         * ==========================
         *   PAROISSE
         * ==========================
         */
        $paroisse = ParoissesFactory::new()->create();

        $DirectionParoisseUser    = UserFactory::new()->DirecteurParoisse()->create();
        $ResponsableParoissesUsers= UserFactory::new()->ResponsableParoisse()->count(2)->create();
        $EmployerParoissesUsers   = UserFactory::new()->EmployerParoisse()->count(5)->create();

        UtilisateurParoisse::factory()->for($DirectionParoisseUser,'user')->for($paroisse,'paroisse')->create();
        $ResponsableParoissesUsers->each(fn($u)=>UtilisateurParoisse::factory()->for($u,'user')->for($paroisse,'paroisse')->create());
        $EmployerParoissesUsers->each(fn($u)=>UtilisateurParoisse::factory()->for($u,'user')->for($paroisse,'paroisse')->create());

        DB::table('availability_slots')->insert([
            'paroisse_id' => $paroisse->id,
            'day_of_week' => "[1,2,3,4,5]",
            'start_time'  => '09:00:00',
            'end_time'    => '18:00:00',
        ]);

        // ---------------------------------------------------
        //ajoute d'une demande entre l'entreprise memorys et la paroisse Diocese
        $nombreDemandes = rand(1, 3);

        DemandeCeremonie::factory()
            ->count($nombreDemandes)
            ->create([
                'entreprise_id'        => $Memorys->id,                 // FK -> entreprise
                'user_entreprise_id'   => $DirecteurMemorys->id,        // FK -> users (OBLIGATOIRE)
                'paroisse_id'          => $Diocese->id,                 // FK -> paroisse
                'users_paroisses_id'   => $userparoises->id // FK -> users_paroisses (nullable mais utile)
            ]);
    }
}
