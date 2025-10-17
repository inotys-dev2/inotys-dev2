<?php
namespace Database\Seeders;

use App\Models\Entreprises;
use App\Models\User;
use App\Models\UtilisateurParoisse;
use Illuminate\Database\Seeder;
use App\Models\Paroisses;
use App\Models\DemandeCeremonie;
use App\Models\Paiement;
use App\Models\FactureParoisse;
use App\Models\Reversement;
use Illuminate\Support\Facades\DB;

class MethodFacturationSeeder extends Seeder
{
    public function run()
    {
        $paroisses = Paroisses::all();
        $pompes = Entreprises::all();

        foreach ($pompes as $pompe) {
            for ($i = 0; $i < 5; $i++) {
                DB::transaction(function () use ($pompe, $paroisses) {
                    $paroisse = $paroisses->random();

                    $demande = DemandeCeremonie::factory()
                        ->for($pompe, 'entreprise')
                        ->for($paroisse, 'paroisse')
                        ->for(UtilisateurParoisse::factory(), 'users_paroisses')
                        ->for(User::factory(), 'createur')
                        ->create([
                            'paroisse_id' => $paroisse->id,
                            'entreprise_id' => $pompe->id,
                        ]);

                    $paiement = Paiement::factory()->create([
                        'demande_ceremonie_id' => $demande->id,
                        'entreprise_id' => $pompe->id,
                    ]);

                    $facture = FactureParoisse::factory()->create([
                        'paroisse_id' => $demande->paroisse_id ?? $demande->paroisse_id,
                        'entreprise_id' => $pompe->id,
                        'client_nom' => $demande->defunt_nom,
                    ]);

                    Reversement::factory()->count(1)->create([
                        'facture_paroissial_id' => $facture->id,
                        'entreprise_id' => $pompe->id,
                        'montant' => $facture->montant_paroissial,
                        'status' => 'recu',
                    ]);
                });
            }
        }
    }
}
