<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AvailabilitySlotsSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Récupère tous les IDs de paroisses
        $paroisseIds = DB::table('paroisses')->pluck('id')->values();

        // 2) Vide la table
        DB::table('availability_slots')
            ->whereIn('paroisses_id', $paroisseIds)
            ->delete();

        $dowGroups = [];
        $maxMask   = 1 << 7;    // 128

        for ($mask = 1; $mask < $maxMask; $mask++) {
            $group = [];
            for ($day = 0; $day < 7; $day++) {
                if ($mask & (1 << $day)) {
                    $group[] = $day;
                }
            }
            $dowGroups[] = $group;
        }

        // 4) Pour chaque paroisse, on choisit UN groupe puis on implode en CSV
        foreach ($paroisseIds as $paroisseId) {
            // Tirage d’un seul sous‑ensemble
            $selectedDays = $dowGroups[array_rand($dowGroups)];
            sort($selectedDays);

            $hour   = rand(8, 11);
            $minute = rand(0, 1) * 30;     // 0 ou 30
            $start  = sprintf('%02d:%02d:00', $hour, $minute);
            $endHour = $hour + 8;
            $end     = sprintf('%02d:%02d:00', $endHour, $minute);

            DB::table('availability_slots')->insert([
                'paroisses_id' => $paroisseId,
                'day_of_week'  => json_encode($selectedDays),   // chaîne "2,3,5,6,7"
                'start_time'   => $start,
                'end_time'     => $end,
            ]);
        }
    }
}
