<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paroisses extends Model
{
    use HasFactory;

    protected $table = 'paroisses';
    public $timestamps = true;
    protected $fillable = [
        'uuid',
        'name',
        'address',
        'city',
        'postal_code',
        'phone',
        'email',
        'capacity',
        'slogan',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'users_paroisses_id', 'paroisses_id', 'users_id')
            ->withPivot('access')
            ->withTimestamps();
    }
    public function availabilitySlots()
    {
        return $this->hasOne(AvailabilitySlot::class, 'paroisses_id');
    }
    public function demandesCeremonies()
    {
        return $this->hasMany(DemandeCeremonie::class, 'paroisses_id');
    }
}
