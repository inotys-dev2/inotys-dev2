<?php

namespace App\Rules;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class WithinAvailability implements ValidationRule
{
    public function __construct(private array $availability) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $dt = Carbon::createFromFormat('Y-m-d H:i', (string)$value);
        if (!$dt) return;

        $iso = $dt->isoWeekday(); // 1..7
        $days = $this->availability['days'] ?? [];

        if (!empty($days) && !in_array($iso, $days, true)) {
            $fail('Ce jour est fermé selon les disponibilités.');
            return;
        }

        $start = $this->availability['start_time'] ?? null;
        $end   = $this->availability['end_time']   ?? null;
        if (!$start && !$end) return;

        $slotMin = $dt->hour * 60 + $dt->minute;
        $toMin = function ($hhmm) {
            if ($hhmm === '24:00') return 1440;
            $h = (int)substr($hhmm,0,2);
            $m = (int)substr($hhmm,3,2);
            return $h*60 + $m;
        };

        $minStart = $start ? $toMin($start) : null;
        $maxEnd   = $end   ? $toMin($end)   : null;

        if ($minStart !== null && $slotMin < $minStart) $fail('En dehors des heures d’ouverture.');
        if ($maxEnd   !== null && $slotMin >= $maxEnd)  $fail('En dehors des heures d’ouverture.');
    }
}
