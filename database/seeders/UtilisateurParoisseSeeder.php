<?php

namespace Database\Seeders;

use App\Models\Paroisses;
use App\Models\UtilisateurParoisse;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UtilisateurEntreprise;
use App\Models\UtilisateurPermission;
use App\Models\Entreprises;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UtilisateurParoisseSeeder extends Seeder
{
    public function run(): void
    {
        UtilisateurParoisse::factory()->count(10)->create();
    }
}
