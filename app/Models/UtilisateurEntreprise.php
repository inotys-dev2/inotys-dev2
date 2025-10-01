<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class UtilisateurEntreprise extends Pivot
{
    use HasFactory;

    public $timestamps = true;

    protected $table = 'users_entreprises';
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'entreprise_id',
        'users_id',
        'access',
    ];

    public function entreprise()
    {
        return $this->belongsTo(Entreprises::class, 'entreprise_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}
