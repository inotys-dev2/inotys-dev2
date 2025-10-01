<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ReversementFactory extends Factory
{
    protected $model = \App\Models\Reversement::class;

    public function definition()
    {
        $status = $this->faker->randomElement(['en_attente', 'recu', 'partiel', 'echoue']);
        $montant = $status === 'partiel'
            ? $this->faker->randomFloat(2, 50, 300)
            : $this->faker->randomFloat(2, 150, 600);

        return [
            'facture_paroissial_id' => \App\Models\FactureParoisse::factory(),
            'entreprise_id' => \App\Models\Entreprises::factory(),
            'montant' => $montant,
            'status' => $status,
            'reference' => 'REV-' . strtoupper($this->faker->bothify('??###')),
            'preuve' => $this->faker->optional()->url,
            'date_initiation' => now()->subDays(rand(1, 10)),
            'date_reception' => $status === 'recu' ? now()->subDays(rand(0, 5)) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
