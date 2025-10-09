<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class UtilisateurParoisse extends Pivot
{

    use HasFactory;

    protected $table = 'users_paroisses';

    public $timestamps = true;
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'paroisse_id',
        'users_id',
        'access',      // votre champ de pivot
    ];

    public function paroisse()
    {
        return $this->belongsTo(Paroisses::class, 'paroisse_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}
