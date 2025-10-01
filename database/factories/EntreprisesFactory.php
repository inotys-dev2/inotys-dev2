<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Entreprises;
use Illuminate\Support\Str;

class EntreprisesFactory extends Factory
{
    protected $model = Entreprises::class;

    public function definition()
    {
        return [
            // Le booted du modèle remplit déjà uuid, mais on peut en fournir un pour les tests reproductibles
            'uuid' => Str::uuid()->toString(),
            'name' => $this->faker->company,
            'address' => $this->faker->streetAddress,
            'city' => $this->faker->city,
            'postal_code' => $this->faker->postcode,
            'phone' => $this->faker->phoneNumber,
            'email' => $this->faker->companyEmail,
            'siret' => $this->faker->numerify('##############'), // 14 chiffres
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
