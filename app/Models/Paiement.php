<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    public $timestamps = true;
    use HasFactory;

    protected $casts = [
        'metadata' => 'array',
        'paye_le' => 'datetime',
    ];

}
