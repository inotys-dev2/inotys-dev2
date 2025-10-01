<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpFoundation\Request;

class DemandeCeremonie extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $table = 'demande_ceremonies';

    // Pour que ces colonnes soient des Carbon instances :
    protected $dates = [
        'date_ceremonie',
        'heure_ceremonie',
    ];

    protected $fillable = [
        'entreprise_id',
        'paroisses_id',
        'users_paroisse_id',
        'nom_defunt',
        'date_ceremonie',
        'heure_ceremonie',
        'duree_minutes',
        'nom_contact_famille',
        'telephone_contact_famille',
        'demandes_speciales',
        'statut',
        'montant',
        'statut_paiement',
        'cree_par',
    ];

    protected $casts = [
        'date_ceremonie' => 'date',
        'heure_ceremonie' => 'datetime:H:i',
        'montant' => 'decimal:2',
    ];

    public function paiement() { return $this->hasOne(Paiement::class, 'demande_ceremonie_id'); }


    // Relations
    public function entreprise()
    {
        return $this->belongsTo(Entreprises::class, 'entreprise_id');
    }

    public function paroisse()
    {
        return $this->belongsTo(Paroisses::class, 'paroisses_id');
    }

    public function users_paroisses()
    {
        return $this->belongsTo(UtilisateurParoisse::class, 'users_paroisses_id');
    }

    public function createur()
    {
        return $this->belongsTo(User::class, 'cree_par');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'demande_ceremonie_id');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'entreprise_id'             => 'required|exists:entreprises,id',
            'paroisses_id'              => 'required|exists:paroisses,id',
            'officiant_id'              => 'required|exists:officiants,id',
            'nom_defunt'                => 'required|string',
            'date_ceremonie'            => 'required|date',
            'heure_ceremonie'           => 'required|date_format:H:i',
            'duree_minutes'             => 'required|integer',
            'nom_contact_famille'       => 'required|string',
            'telephone_contact_famille' => 'required|string',
            'demandes_speciales'        => 'nullable|string',
            'statut'                    => 'nullable|string',
            'montant'                   => 'nullable|numeric',
            'statut_paiement'           => 'nullable|string',
            'cree_par'                  => 'required|exists:users,id',
        ]);

        // Création rapide grâce à la méthode statique create()
        $demande = DemandeCeremonie::create($data);

        // … redirection ou retour JSON, etc.
        return redirect()->route('entreprise.agenda.demande', $demande);
    }
}
