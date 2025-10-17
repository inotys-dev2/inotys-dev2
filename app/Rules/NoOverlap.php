<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\DemandeCeremonie;

class NoOverlap implements ValidationRule
{
    public function __construct(private int $paroisseId, private ?int $ignoreId = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $req = request();
        $start = $req->input('start');
        $end   = $req->input('end');
        if (!$start || !$end) return;

        $q = DemandeCeremonie::query()
            ->where('paroisse_id', $this->paroisseId)
            ->when($this->ignoreId, fn($qq) => $qq->where('id','!=',$this->ignoreId))
            ->where(function($qq) use ($start,$end) {
                $qq->whereBetween('start', [$start, $end])
                    ->orWhereBetween('end',   [$start, $end])
                    ->orWhere(function($q2) use ($start,$end){
                        $q2->where('start','<=',$start)->where('end','>=',$end);
                    });
            });

        if ($q->exists()) {
            $fail('Ce créneau chevauche une autre cérémonie pour cette paroisse.');
        }
    }
}
