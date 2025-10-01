<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UtilisateurPermission extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $table = 'users_perms';

    protected $fillable = [
        'users_id',
        'entreprise_id',
        'permission_employe_creer_demande',
        'permission_employe_voir_demande',
        'permission_employe_modifier_demande',
        'permission_employe_valider_demande',
        'permission_employe_refuser_demande',
        'permission_responsable_gerer_agenda',
        'permission_responsable_generer_paiement',
        'permission_responsable_suivre_paiement',
        'permission_responsable_confirmer_officiant',
        'permission_administrateur_gerer_utilisateur',
        'permission_administrateur_configurer_systeme',
        'permission_administrateur_voir_historique',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    // Relations
    public function entreprise()
    {
        return $this->belongsTo(Entreprises::class, 'entreprise_id');
    }
}


