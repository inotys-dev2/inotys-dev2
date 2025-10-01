<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    Use HasFactory;

    public $timestamps = true;

    protected $table = 'users';

    protected $casts = [
        'last_seen' => 'datetime',
    ];

    protected $fillable = [
        'id',
        'email',
        'password',
        'prenom',
        'nom',
        'telephone',
        'access',
        'role',
        'last_seen',
        'theme'
    ];

    protected $hidden = [
        'password',
    ];

    public function getAuthPassword()
    {
        return $this->password;
    }


    public function userByid($id)
    {
        return $this->user()->where('id', $id)->first();
    }

    public function users_paroisses(): HasMany
    {
        return $this->HasMany(UtilisateurParoisse::class, 'users_id');
    }

    public function users_entreprises(): HasMany
    {
        return $this->hasMany(UtilisateurEntreprise::class, 'users_id');
    }

    public function entreprises(): BelongsToMany
    {
        return $this->belongsToMany(
            Entreprises::class,
            'users_entreprises',
            'users_id',        // colonne dans users_entreprises qui pointe sur users.id
            'entreprise_id'    // colonne dans users_entreprises qui pointe sur entreprise.id
        )
            ->using(UtilisateurEntreprise::class)
            ->withPivot('rank')
            ->withTimestamps();
    }

    public function paroisses(): BelongsToMany
    {
        return $this->belongsToMany(
            Paroisses::class,
            'users_paroisses',
            'users_id',        // colonne dans users_entreprises qui pointe sur users.id
            'paroisses_id'    // colonne dans users_entreprises qui pointe sur entreprise.id
        )
            ->using(UtilisateurParoisse::class)
            ->withPivot('rank')
            ->withTimestamps();
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'users_id');
    }

    public function isOnlineFromSession(int $thresholdMinutes = 5): bool
    {
        // on calcule le timestamp plancher
        $cutoff = Carbon::now()->subMinutes($thresholdMinutes)->getTimestamp();

        $count = DB::table('sessions')
            ->where('user_id', $this->id)
            ->where('last_activity', '>=', $cutoff)
            ->count();

        return $count > 0;
    }

    public function perms(): HasOne
    {
        return $this->hasOne(UtilisateurPermission::class, 'users_id', 'id');
    }

    /**
     * 2) Vérifie si l’utilisateur a la permission demandée.
     *
     * @param  string  $permissionKey  Le nom de la colonne de permission, ex. 'permission_responsable_valider_demande'
     * @return bool
     */
    public function hasPermission(string $permissionKey): bool
    {
        $perms = $this->perms()->first() ?? new UtilisateurPermission();

        $attribute = str_replace('.', '_', $permissionKey);

        if (! isset($perms->$attribute)) {
            return false;
        }

        // On caste directement la valeur en bool
        return (bool) $perms->$attribute;
    }

    /**
     * 3) Attribue un jeu de permissions à l’utilisateur.
     *
     * @param  array  $permissions  Tableau associatif ['permission_xxx' => true|false, …]
     * @return UtilisateurPermission
     */
    public function setPermissions(array $permissions): UtilisateurPermission
    {
        // Récupère ou crée le modèle
        $perms = $this->perms()->firstOrNew();

        // Remplit uniquement les colonnes existantes
        foreach ($permissions as $key => $value) {
            if (in_array($key, $perms->getFillable(), true)) {
                $perms->$key = (bool) $value;
            }
        }

        // Sauvegarde en base
        $perms->users_id = $this->id;
        $perms->save();

        return $perms;
    }
}
