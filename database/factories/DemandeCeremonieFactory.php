<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DemandeCeremonieFactory extends Factory
{
    protected $model = \App\Models\DemandeCeremonie::class;

    public function definition()
    {
        $status = $this->faker->randomElement(['treatment', 'waiting', 'accepted', 'passed']);
        $score = $this->faker->numberBetween(0, 5);
        $sum = $this->faker->randomFloat(2, 100, 1000);

        return [
            'deceased_name' => $this->faker->name,
            'ceremony_date' => $this->faker->dateTimeBetween('+1 days', '+10 days')->format('Y-m-d'),
            'ceremony_hour' => $this->faker->time(),
            'duration_time' => $this->faker->numberBetween(30, 180),
            'contact_family_name' => $this->faker->name(),
            'telephone_contact_family' => $this->faker->phoneNumber(),
            'special_requests' => $this->faker->optional()->sentence(),
            'score' => $score,
            'statut' => $status,
            'sum' => $sum,
            'statut_paiement' => $this->faker->randomElement(['define', 'waiting']),
        ];
    }
}
