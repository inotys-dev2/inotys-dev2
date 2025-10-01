<?php

namespace Database\Seeders;

use App\Models\Paroisses;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ParoissesSeeder extends Seeder
{
    public function run(): void
    {
        Paroisses::factory()->count(5)->create();
    }
}
