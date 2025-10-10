<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemandeCeremonie extends Model
{
    // Si ta table suit la convention (demande_ceremonies), inutile de préciser $table.
    use HasFactory;

    protected $fillable = [
        'entreprise_id',
        'user_entreprise_id',
        'paroisse_id',
        'users_paroisses_id',
        'assigned_user_id',
        'deceased_name',
        'ceremony_date',
        'ceremony_hour',
        'duration_time',
        'contact_family_name',
        'telephone_contact_family',
        'special_requests',
        'statut',
        'sum',
        'statut_paiement',
        'score',
        'cancel_reason',
    ];

    protected $casts = [
        'ceremony_date'     => 'date',       // Y-m-d
        // Laravel n’a pas de cast "time" natif : on garde string pour ceremony_hour
        'duration_time'     => 'integer',
        'sum'               => 'decimal:2',
        'score'             => 'integer',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
    ];

    // Attribut virtuel (appended) combinant date + heure en instance Carbon
    protected $appends = ['ceremony_at'];

    /* ---------------------------------
     | Relations
     |----------------------------------*/

    public function entreprise()
    {
        return $this->belongsTo(Entreprises::class);
    }

    public function userEntreprise()
    {
        return $this->belongsTo(User::class, 'user_entreprise_id');
    }

    public function paroisse()
    {
        return $this->belongsTo(Paroisses::class);
    }

    public function userParoisse()
    {
        return $this->belongsTo(UtilisateurParoisse::class, 'users_paroisses_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    /* ---------------------------------
     | Accessors / Mutators
     |----------------------------------*/

    // Retourne la date+heure sous forme Carbon (null-safe)
    public function getCeremonyAtAttribute(): ?Carbon
    {
        if (!$this->ceremony_date || !$this->ceremony_hour) {
            return null;
        }

        // ceremony_hour peut être "HH:MM[:SS]" -> on normalise
        $hour = strlen($this->ceremony_hour) === 5 ? $this->ceremony_hour . ':00' : $this->ceremony_hour;

        return Carbon::parse($this->ceremony_date->format('Y-m-d') . ' ' . $hour);
    }

    // (Optionnel) Force un format HH:MM en entrée
    public function setCeremonyHourAttribute($value): void
    {
        if (empty($value)) {
            $this->attributes['ceremony_hour'] = null;
            return;
        }

        // Accepte "9:5", "09:5", "09:05", "09:05:00"
        $time = substr($value, 0, 8); // max HH:MM:SS
        $parts = explode(':', $time);
        $h = str_pad($parts[0] ?? '00', 2, '0', STR_PAD_LEFT);
        $m = str_pad($parts[1] ?? '00', 2, '0', STR_PAD_LEFT);

        $this->attributes['ceremony_hour'] = "$h:$m";
    }

    /* ---------------------------------
     | Scopes pratiques
     |----------------------------------*/

    public function scopeStatus(Builder $q, string $status): Builder
    {
        return $q->where('statut', $status);
    }

    public function scopePaymentStatus(Builder $q, string $status): Builder
    {
        return $q->where('statut_paiement', $status);
    }

    public function scopeForEntreprise(Builder $q, int $entrepriseId): Builder
    {
        return $q->where('entreprise_id', $entrepriseId);
    }

    public function scopeAssignedTo(Builder $q, int $userId): Builder
    {
        return $q->where('assigned_user_id', $userId);
    }

    // Entre deux dates (basé sur ceremony_date)
    public function scopeBetweenDates(Builder $q, $from, $to): Builder
    {
        return $q->whereBetween('ceremony_date', [$from, $to]);
    }

    // À venir (date > aujourd’hui ou aujourd’hui avec heure future)
    public function scopeUpcoming(Builder $q): Builder
    {
        $today = Carbon::today();
        return $q->where(function ($sub) use ($today) {
            $sub->where('ceremony_date', '>', $today)
                ->orWhere(function ($sub2) use ($today) {
                    $sub2->whereDate('ceremony_date', $today)
                        ->where('ceremony_hour', '>=', now()->format('H:i'));
                });
        });
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        if (!$term) return $q;

        return $q->where(function ($sub) use ($term) {
            $sub->where('deceased_name', 'like', "%$term%")
                ->orWhere('contact_family_name', 'like', "%$term%")
                ->orWhere('telephone_contact_family', 'like', "%$term%");
        });
    }
}
