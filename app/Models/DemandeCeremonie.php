<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'score',
        'cancel_reason',
    ];

    protected $casts = [
        'ceremony_date'     => 'date:Y-m-d',       // Y-m-d
        'duration_time'     => 'integer',
        'score'             => 'integer',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
//        'contact_family_name'  => 'encrypted',
//        'contact_family_phone' => 'encrypted',
    ];

    // Attribut virtuel (appended) combinant date + heure en instance Carbon
    protected $appends = ['ceremony_at'];

    protected function contactFamilyName(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if ($value === null || $value === '') return $value;
                try { return decrypt($value); } catch (\Throwable $e) { return $value; }
            },
            set: fn ($value) => $value ? encrypt($value) : null,
        );
    }

    protected function contactFamilyPhone(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if ($value === null || $value === '') return $value;
                try { return decrypt($value); } catch (\Throwable $e) { return $value; }
            },
            set: fn ($value) => $value ? encrypt($value) : null,
        );
    }

    /* ---------------------------------
     | Relations
     |----------------------------------*/

    public function getCeremonyAtAttribute(): ?string
    {
        if (empty($this->ceremony_date)) return null;

        $date = $this->ceremony_date instanceof Carbon
            ? $this->ceremony_date
            : Carbon::parse($this->ceremony_date);

        $hour = $this->ceremony_hour ?: '00:00:00';
        if (preg_match('/^\d{2}:\d{2}$/', $hour)) {
            $hour .= ':00';
        }
        [$H,$M,$S] = array_pad(explode(':', $hour), 3, '00');

        return $date->copy()->setTime((int)$H,(int)$M,(int)$S)->toIso8601String();
    }


    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprises::class);
    }

    public function userEntreprise(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_entreprise_id');
    }

    public function paroisse(): BelongsTo
    {
        return $this->belongsTo(Paroisses::class);
    }

    public function userParoisse(): BelongsTo
    {
        return $this->belongsTo(UtilisateurParoisse::class, 'users_paroisses_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_at');
    }
}
