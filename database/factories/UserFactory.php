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
            'telephone' => $this->faker->phoneNumber,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function EmployerEntreprise() {
        return $this->state([
            'access' => 'entreprise',
            'role' => $this->faker->randomElement(['Conseiller funéraire', 'Thanatopracteur', 'Porteur/Chauffeur/Fossoyer', 'Assistant administratif', 'Conseiller en prévoyance funéraire', 'Psychologue']),
            'theme' => 'memorys'
        ]);
    }

    public function ResponsableEntreprise() {
        return $this->state([
            'access' => 'entreprise',
            'role' => $this->faker->randomElement(['Conseiller funéraire principal','Maître de cérémonie', 'Responsable administratif']),
            'theme' => 'memorys'
        ]);
    }

    public function DirectionEntreprise() {
        return $this->state([
            'access' => 'entreprise',
            'role' => $this->faker->randomElement(['Directeur funéraire', 'Responsable logistique', 'Responsable commercial']),
            'theme' => 'memorys'
        ]);
    }


    public function EmployerParoisse() {
        return $this->state([
           'access' => 'paroisse',
           'role' => $this->faker->randomElement(['Vicaire', 'Diacre', 'Animateur patoral', 'Catéchiste']),
           'theme' => 'default',
        ]);
    }

    public function ResponsableParoisse() {
        return $this->state([
            'access' => 'paroisse',
            'role' => $this->faker->randomElement(['Secrétaire paroissial', 'Trésoirier', 'Resp Communication', 'Resp Logistique']),
            'theme' => 'default',
        ]);
    }

    public function DirecteurParoisse() {
        return $this->state([
            'access' => 'paroisse',
            'role' => $this->faker->randomElement(['Prêtre', 'Curé']),
            'theme' => 'default',
        ]);
    }

}
