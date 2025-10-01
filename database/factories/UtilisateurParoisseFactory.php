<?php

namespace Database\Factories;

use App\Models\Paroisses;
use App\Models\UtilisateurParoisse;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\UtilisateurEntreprise;
use App\Models\User;
use App\Models\Entreprises;

class UtilisateurParoisseFactory extends Factory
{
    protected $model = UtilisateurParoisse::class;

    public function definition()
    {
        return [
            'users_id'      => User::factory(),
            'paroisses_id'  => Paroisses::factory(),
            'rank'          => $this->faker->randomElement(['Directeur', 'Responsable', 'Employer', 'Stagiaire', 'Interim']),
        ];
    }
}
