<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Entreprises extends Model
{

    use HasFactory;

    public $timestamps = true;

    protected $table = 'entreprise';

    protected $fillable = [
        'name',
        'address',
        'city',
        'postal_code',
        'profileImg',
        'phone',
        'email',
        'siret',
        'uuid'
    ];


    /**
     * Toutes les liaisons de pivot Entreprise_User
     */
    public function entreprise_user(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'users_entreprises',
            'entreprise_id',
            'users_id'
        );
    }

    // Exemple de relation avec les demandes de cérémonie
    public function demandesCeremonies()
    {
        return $this->hasMany(DemandeCeremonie::class, 'entreprise_id');
    }

    protected static function booted()
    {
        static::creating(function ($entreprise) {
            return $entreprise->uuid = Str::uuid();
        });
    }
}
