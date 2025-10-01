<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Paroisses;
use Illuminate\Support\Str;

class ParoissesFactory extends Factory
{
    protected $model = Paroisses::class;

    public function definition()
    {
        return [
            'uuid' => Str::uuid(),
            'name' => $this->faker->company . ' Paroisse',
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'postal_code' => $this->faker->postcode,
            'phone' => $this->faker->phoneNumber,
            'email' => $this->faker->safeEmail,
            'capacity' => $this->faker->numberBetween(10, 100),
            'slogan' => Str::limit($this->faker->sentence(10), 30),
            'created_at' => now(),
            'updated_at' => now(),
            ];
    }
}
