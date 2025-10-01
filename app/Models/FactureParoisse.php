<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FactureParoisse extends Model
{
    protected $table = 'factures_paroisse'; // ou 'facture_paroisses' selon ce qui existe

    public $timestamps = true;
    public $incrementing = false;
    protected $keyType = 'string';

    use HasFactory;

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function paroisse() { return $this->belongsTo(Paroisses::class); }
    public function pompeFunebre() { return $this->belongsTo(Entreprises::class); }
    public function reversements() { return $this->hasMany(Reversement::class, 'facture_paroissial_id'); }

}
