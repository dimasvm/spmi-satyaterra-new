<?php

namespace Database\Seeders;

use App\Enums\UnitType;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Seed initial campus units.
     */
    public function run(): void
    {
        $units = [
            [
                'code' => 'LPM',
                'name' => 'Lembaga Penjaminan Mutu',
                'type' => UnitType::Institution,
            ],
            [
                'code' => 'BAAK',
                'name' => 'Biro Administrasi Akademik dan Kemahasiswaan',
                'type' => UnitType::Bureau,
            ],
            [
                'code' => 'FST',
                'name' => 'Fakultas Sains dan Teknologi',
                'type' => UnitType::Faculty,
            ],
            [
                'code' => 'FEB',
                'name' => 'Fakultas Ekonomi dan Bisnis',
                'type' => UnitType::Faculty,
            ],
            [
                'code' => 'FKIP',
                'name' => 'Fakultas Keguruan dan Ilmu Pendidikan',
                'type' => UnitType::Faculty,
            ],
            [
                'code' => 'TI',
                'name' => 'Program Studi Teknik Informatika',
                'type' => UnitType::StudyProgram,
            ],
            [
                'code' => 'SI',
                'name' => 'Program Studi Sistem Informasi',
                'type' => UnitType::StudyProgram,
            ],
            [
                'code' => 'MNJ',
                'name' => 'Program Studi Manajemen',
                'type' => UnitType::StudyProgram,
            ],
            [
                'code' => 'AKT',
                'name' => 'Program Studi Akuntansi',
                'type' => UnitType::StudyProgram,
            ],
            [
                'code' => 'PGSD',
                'name' => 'Program Studi Pendidikan Guru Sekolah Dasar',
                'type' => UnitType::StudyProgram,
            ],
        ];

        foreach ($units as $unit) {
            Unit::updateOrCreate(
                ['code' => $unit['code']],
                [
                    'parent_id' => null,
                    'name' => $unit['name'],
                    'type' => $unit['type']->value,
                    'is_active' => true,
                ],
            );
        }
    }
}
