<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DemandeCeremonieFactory extends Factory
{
    protected $model = \App\Models\DemandeCeremonie::class;

    public function definition()
    {
        return [
            'nom_defunt' => $this->faker->name,
            'date_ceremonie' => $this->faker->dateTimeBetween('+1 days', '+10 days'),
            'heure_ceremonie' => $this->faker->time('H:i:s'),
            'duree_minutes' => $this->faker->numberBetween(30, 180),
            'nom_contact_famille' => $this->faker->optional()->name(),
            'telephone_contact_famille' => $this->faker->optional()->phoneNumber(),
            'demandes_speciales' => $this->faker->optional()->sentence(),
            'statut' => $this->faker->randomElement(['en_attente', 'acceptee', 'refusee', 'passee']),
            'montant' => $this->faker->optional()->randomFloat(2, 100, 1000),
            'statut_paiement' => $this->faker->randomElement(['en_attente', 'paye', 'annule']),
        ];
    }

}
