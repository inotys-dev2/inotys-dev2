<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\UtilisateurPermission;
use App\Models\User;
use App\Models\Entreprises;

class UtilisateurPermissionFactory extends Factory
{
    protected $model = UtilisateurPermission::class;

    public function definition(): array
    {
        return [
            'users_id' => User::factory(),
            'entreprise_id' => Entreprises::factory(),
            'permission_employe_creer_demande' => true,
            'permission_employe_voir_demande' => true,
            'permission_employe_modifier_demande' => true,
            'permission_responsable_valider_demande' => false,
            'permission_responsable_refuser_demande' => false,
            'permission_responsable_gerer_agenda' => false,
            'permission_responsable_generer_paiement' => false,
            'permission_responsable_suivre_paiement' => false,
            'permission_responsable_confirmer_officiant' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function responsable(): UtilisateurPermissionFactory
    {
        return $this->state(fn() => [
            'permission_responsable_valider_demande' => true,
            'permission_responsable_refuser_demande' => true,
            'permission_responsable_gerer_agenda' => true,
            'permission_responsable_generer_paiement' => true,
            'permission_responsable_suivre_paiement' => true,
            'permission_responsable_confirmer_officiant' => true,
        ]);
    }

    public function admin(): UtilisateurPermissionFactory
    {
        return $this->state(fn() => [
            'permission_responsable_valider_demande' => true,
            'permission_responsable_refuser_demande' => true,
            'permission_responsable_gerer_agenda' => true,
            'permission_responsable_generer_paiement' => true,
            'permission_responsable_suivre_paiement' => true,
            'permission_responsable_confirmer_officiant' => true,
            'permission_administrateur_gerer_utilisateur' => true,
            'permission_administrateur_configurer_systeme' => true,
            'permission_administrateur_voir_historique' => true,
        ]);
    }
}
