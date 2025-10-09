<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvailabilitySlot extends Model
{

    use HasFactory;

    public $timestamps = true;
    protected $table = "availability_slots";

    protected $fillable = [
        'paroisse_id',
        'day_of_week',
        'start_time',
        'end_time',];

    protected $casts = [
        'day_of_week' => 'array',
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
    ];

    // Relation vers la paroisse
    public function paroisse()
    {
        return $this->belongsTo(Paroisses::class, 'paroisse_id');
    }
}
