<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class FactureParoisseFactory extends Factory
{
    protected $model = \App\Models\FactureParoisse::class;

    public function definition()
    {
        $montant = $this->faker->randomFloat(2, 150, 600);
        $taxes = round($montant * 0.2, 2); // ex. TVA 20%
        $total = $montant + $taxes;

        return [
            'paroisses_id' => \App\Models\Paroisses::factory(),
            'entreprise_id' => \App\Models\Entreprises::factory(),
            'client_nom' => $this->faker->name,
            'description' => 'Cérémonie religieuse pour ' . $this->faker->name,
            'montant_paroissial' => $montant,
            'taxes' => $taxes,
            'total' => $total,
            'statut' => $this->faker->randomElement(['envoye', 'en_attente_reversement', 'partiellement_reverse', 'regle']),
            'reference_externe' => 'FP-' . strtoupper($this->faker->bothify('???-#####')),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
