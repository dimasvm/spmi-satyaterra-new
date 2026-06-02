<?php

namespace Database\Seeders;

use App\Models\SpmiPeriod;
use Illuminate\Database\Seeder;

class SpmiPeriodSeeder extends Seeder
{
    /**
     * Seed the active SPMI period.
     */
    public function run(): void
    {
        SpmiPeriod::updateOrCreate(
            [
                'academic_year' => '2025/2026',
                'semester' => 'tahunan',
            ],
            [
                'name' => 'Periode SPMI 2025/2026',
                'start_date' => '2025-09-01',
                'end_date' => '2026-08-31',
                'status' => 'active',
            ],
        );
    }
}
