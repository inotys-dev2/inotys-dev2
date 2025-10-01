<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PompeFunebreFactory extends Factory
{
    protected $model = \App\Models\Entreprises::class;

    public function definition()
    {
        return [
            'nom' => $this->faker->company . ' Pompes Funèbres',
            'email' => $this->faker->companyEmail,
            'telephone' => $this->faker->phoneNumber,
            'adresse' => $this->faker->address,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
