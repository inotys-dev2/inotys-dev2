<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AutomatiqueSeeder::class
            /*EntreprisesSeeder::class,
            ParoissesSeeder::class,

            GeneratingAdminUsers::class,
            UtilisateursSeeder::class,

            UtilisateurEntrepriseSeeder::class,
            UtilisateurParoisseSeeder::class,

            AvailabilitySlotsSeeder::class,
           // DemandeCeremoniesSeeder::class,
            MethodFacturationSeeder::class,*/
        ]);
    }
}
