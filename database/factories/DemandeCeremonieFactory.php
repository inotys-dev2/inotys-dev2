<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DemandeCeremonieFactory extends Factory
{
    protected $model = \App\Models\DemandeCeremonie::class;

    public function definition()
    {
        $status = $this->faker->randomElement(['a_traiter', 'passee']);
        $score = 0;
        $montant = $this->faker->randomFloat(2, 100, 1000);

        if($status == 'a_traiter') {
            $score = $this->faker->numberBetween(0, 5);
        } else if($status == 'passee'){
            $montant = 0;
        }

        return [
            'nom_defunt' => $this->faker->name,
            'date_ceremonie' => $this->faker->dateTimeBetween('+1 days', '+10 days')->format('Y-m-d'),
            'heure_ceremonie' => $this->faker->time(),
            'duree_minutes' => $this->faker->numberBetween(30, 180),
            'nom_contact_famille' => $this->faker->name(),
            'telephone_contact_famille' => $this->faker->phoneNumber(),
            'demandes_speciales' => $this->faker->optional()->sentence(),
            'score' => $score,
            'statut' => $status,
            'montant' => $montant,
            'statut_paiement' => $this->faker->randomElement(['en_attente']),
        ];
    }
}
