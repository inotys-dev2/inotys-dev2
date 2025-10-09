<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\UtilisateurEntreprise;
use App\Models\User;
use App\Models\Entreprises;

class UtilisateurEntrepriseFactory extends Factory
{
    protected $model = UtilisateurEntreprise::class;
    public function definition()
    {
        return [
            'users_id'      => User::factory(),
            'entreprise_id' => Entreprises::factory(),
        ];
    }
}
