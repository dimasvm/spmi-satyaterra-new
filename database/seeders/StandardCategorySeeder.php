<?php

namespace Database\Seeders;

use App\Models\StandardCategory;
use Illuminate\Database\Seeder;

class StandardCategorySeeder extends Seeder
{
    /**
     * Seed initial SPMI standard categories.
     */
    public function run(): void
    {
        $categories = [
            [
                'code' => 'PDD',
                'name' => 'Pendidikan',
                'description' => 'Standar mutu terkait proses, isi, penilaian, dan capaian pembelajaran.',
            ],
            [
                'code' => 'PNL',
                'name' => 'Penelitian',
                'description' => 'Standar mutu terkait perencanaan, pelaksanaan, luaran, dan publikasi penelitian.',
            ],
            [
                'code' => 'PKM',
                'name' => 'Pengabdian kepada Masyarakat',
                'description' => 'Standar mutu terkait kegiatan pengabdian dan dampaknya bagi masyarakat.',
            ],
            [
                'code' => 'TKP',
                'name' => 'Tata Kelola dan Kerja Sama',
                'description' => 'Standar mutu terkait tata pamong, tata kelola, penjaminan mutu, dan kerja sama.',
            ],
            [
                'code' => 'SDM',
                'name' => 'Sumber Daya Manusia',
                'description' => 'Standar mutu terkait dosen dan tenaga kependidikan.',
            ],
            [
                'code' => 'SAR',
                'name' => 'Sarana dan Prasarana',
                'description' => 'Standar mutu terkait ketersediaan dan kelayakan sarana prasarana.',
            ],
        ];

        foreach ($categories as $category) {
            StandardCategory::updateOrCreate(
                ['code' => $category['code']],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                ],
            );
        }
    }
}
