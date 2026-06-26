<?php

namespace Database\Seeders;

use App\Models\CampusProfile;
use Illuminate\Database\Seeder;

class CampusProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faculties = [
            [
                'name' => 'Fakultas Teknik & Ilmu Komputer',
                'total_students' => 2950,
                'total_lecturers' => 99,
                'study_programs' => [
                    [
                        'name' => 'Teknik Informatika',
                        'level' => 'S1',
                        'accreditation' => 'Unggul',
                        'total_students' => 1200,
                        'total_lecturers' => 40,
                    ],
                    [
                        'name' => 'Sistem Informasi',
                        'level' => 'S1',
                        'accreditation' => 'Baik Sekali',
                        'total_students' => 850,
                        'total_lecturers' => 28,
                    ],
                    [
                        'name' => 'Teknik Sipil',
                        'level' => 'S1',
                        'accreditation' => 'Baik Sekali',
                        'total_students' => 500,
                        'total_lecturers' => 17,
                    ],
                    [
                        'name' => 'Teknik Elektro',
                        'level' => 'S1',
                        'accreditation' => 'Baik',
                        'total_students' => 400,
                        'total_lecturers' => 14,
                    ],
                ],
            ],
            [
                'name' => 'Fakultas Ekonomi & Bisnis',
                'total_students' => 2790,
                'total_lecturers' => 93,
                'study_programs' => [
                    [
                        'name' => 'Manajemen',
                        'level' => 'S1',
                        'accreditation' => 'Unggul',
                        'total_students' => 1500,
                        'total_lecturers' => 50,
                    ],
                    [
                        'name' => 'Akuntansi',
                        'level' => 'S1',
                        'accreditation' => 'Baik Sekali',
                        'total_students' => 980,
                        'total_lecturers' => 33,
                    ],
                    [
                        'name' => 'Ekonomi Pembangunan',
                        'level' => 'S1',
                        'accreditation' => 'Baik',
                        'total_students' => 310,
                        'total_lecturers' => 10,
                    ],
                ],
            ],
            [
                'name' => 'Fakultas Ilmu Sosial & Humaniora',
                'total_students' => 800,
                'total_lecturers' => 26,
                'study_programs' => [
                    [
                        'name' => 'Ilmu Komunikasi',
                        'level' => 'S1',
                        'accreditation' => 'Unggul',
                        'total_students' => 600,
                        'total_lecturers' => 20,
                    ],
                    [
                        'name' => 'Hukum',
                        'level' => 'S1',
                        'accreditation' => 'Baik Sekali',
                        'total_students' => 200,
                        'total_lecturers' => 6,
                    ],
                ],
            ],
        ];

        $studentStats = [
            'labels' => [
                'S1 Manajemen',
                'S1 Teknik Informatika',
                'S1 Akuntansi',
                'S1 Sistem Informasi',
                'S1 Ilmu Komunikasi',
                'S1 Teknik Sipil',
                'S1 Teknik Elektro',
                'S1 Ekonomi Pembangunan',
                'S1 Hukum',
            ],
            'data' => [1500, 1200, 980, 850, 600, 500, 400, 310, 200],
        ];

        $accreditationStats = [
            'labels' => ['Baik Sekali', 'Unggul', 'Baik'],
            'data' => [4, 3, 2],
        ];

        $rawData = [
            'detail' => [
                'nama_pt' => 'Universitas Satyaterra',
                'kode_pt' => '40102030',
                'jenis_pt' => 'Universitas',
                'stat_pt' => 'Aktif',
                'nilai_akreditasi' => 'Unggul',
                'alamat' => 'Jl. Pahlawan No. 123, Kel. Merdeka',
                'kab_kota' => 'Kota Bandung',
                'provinsi' => 'Jawa Barat',
                'no_tel' => '(022) 123-4567',
                'email' => 'info@satyaterra.ac.id',
                'website' => 'https://satyaterra.ac.id',
                'jumlah_mahasiswa' => 6540,
                'jumlah_dosen' => 218,
            ],
            'jabatan_fungsional' => [
                'Lektor Kepala' => 25,
                'Lektor' => 110,
                'Asisten Ahli' => 65,
                'Tenaga Pengajar' => 18,
            ],
        ];

        // Deactivate other campuses to ensure this is active
        CampusProfile::query()->update(['is_active' => false]);

        CampusProfile::updateOrCreate(
            ['pddikti_id' => 'mock-satyaterra-id'],
            [
                'name' => 'Universitas Satyaterra',
                'short_name' => 'US',
                'npsn' => '40102030',
                'accreditation' => 'Unggul',
                'status' => 'Aktif',
                'type' => 'Universitas',
                'address' => 'Jl. Pahlawan No. 123, Kel. Merdeka',
                'province' => 'Jawa Barat',
                'city' => 'Kota Bandung',
                'phone' => '(022) 123-4567',
                'email' => 'info@satyaterrabhinneka.ac.id/',
                'website' => 'https://satyaterrabhinneka.ac.id/',
                'logo_url' => null, // Will use default placeholder SVG
                'total_students' => 6540,
                'total_lecturers' => 218,
                'total_study_programs' => 9,
                'faculties' => $faculties,
                'student_stats' => $studentStats,
                'accreditation_stats' => $accreditationStats,
                'raw_data' => $rawData,
                'is_active' => true,
                'last_synced_at' => now(),
            ]
        );
    }
}
