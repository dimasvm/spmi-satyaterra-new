<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Seed initial campus units.
     */
    public function run(): void
    {
        $root = Unit::updateOrCreate(
            ['code' => 'USTB'],
            [
                'parent_id' => null,
                'name' => 'Universitas Satya Terra Bhinneka',
                'type' => 'university',
                'is_active' => true,
            ],
        );

        $units = [
            [
                'code' => 'LPM',
                'name' => 'Lembaga Penjaminan Mutu',
                'type' => 'institution',
            ],
            [
                'code' => 'BAAK',
                'name' => 'Biro Administrasi Akademik dan Kemahasiswaan',
                'type' => 'bureau',
            ],
            [
                'code' => 'FST',
                'name' => 'Fakultas Sains dan Teknologi',
                'type' => 'faculty',
            ],
            [
                'code' => 'FEB',
                'name' => 'Fakultas Ekonomi dan Bisnis',
                'type' => 'faculty',
            ],
            [
                'code' => 'TI',
                'name' => 'Program Studi Teknik Informatika',
                'type' => 'study_program',
            ],
            [
                'code' => 'SI',
                'name' => 'Program Studi Sistem Informasi',
                'type' => 'study_program',
            ],
            [
                'code' => 'MNJ',
                'name' => 'Program Studi Manajemen',
                'type' => 'study_program',
            ],
        ];

        foreach ($units as $unit) {
            Unit::updateOrCreate(
                ['code' => $unit['code']],
                [
                    'parent_id' => $root->id,
                    'name' => $unit['name'],
                    'type' => $unit['type'],
                    'is_active' => true,
                ],
            );
        }
    }
}
