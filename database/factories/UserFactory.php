<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'prenom' => $this->faker->firstName,
            'nom' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'password' => bcrypt('secret123'),
            'access' => $this->faker->randomElement(['entreprise', 'paroisses']),
            'role' => 'Membre',
            'telephone' => $this->faker->phoneNumber,
            'theme' => $this->faker->randomElement(['default', 'memorys']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
