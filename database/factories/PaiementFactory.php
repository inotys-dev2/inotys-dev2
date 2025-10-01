<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PaiementFactory extends Factory
{
    protected $model = \App\Models\Paiement::class;

    public function definition()
    {
        $paroissePart = $this->faker->randomFloat(2, 100, 500); // ex 100€ à 500€
        $pompePart = $this->faker->randomFloat(2, 500, 1500);
        $total = $paroissePart + $pompePart;

        return [
            'demande_ceremonie_id' => \App\Models\DemandeCeremonie::factory(),
            'entreprise_id' => \App\Models\Entreprises::factory(),
            'montant_total' => $total,
            'methode_paiement' => $this->faker->randomElement(['carte', 'virement', 'paypal']),
            'provider_payment_id' => 'pay_' . $this->faker->unique()->regexify('[A-Za-z0-9]{16}'),
            'metadata' => [
                'paroisse' => $paroissePart,
                'pompe' => $pompePart,
            ],
            'statut' => 'paye',
            'paye_le' => now()->subMinutes(rand(1, 120)),
            'reference_paiement' => 'REF-' . strtoupper($this->faker->bothify('??###')),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
