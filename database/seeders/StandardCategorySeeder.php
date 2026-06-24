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
            'PDD' => [
                'name' => 'Pendidikan',
                'description' => 'Standar mutu terkait proses, isi, penilaian, dan capaian pembelajaran.',
            ],
            'PNL' => [
                'name' => 'Penelitian',
                'description' => 'Standar mutu terkait perencanaan, pelaksanaan, luaran, dan publikasi penelitian.',
            ],
            'PKM' => [
                'name' => 'Pengabdian kepada Masyarakat',
                'description' => 'Standar mutu terkait kegiatan pengabdian dan dampaknya bagi masyarakat.',
            ],
            'TKP' => [
                'name' => 'Tata Kelola dan Kerja Sama',
                'description' => 'Standar mutu terkait tata pamong, tata kelola, penjaminan mutu, dan kerja sama.',
            ],
            'SDM' => [
                'name' => 'Sumber Daya Manusia',
                'description' => 'Standar mutu terkait dosen dan tenaga kependidikan.',
            ],
            'SAR' => [
                'name' => 'Sarana dan Prasarana',
                'description' => 'Standar mutu terkait ketersediaan dan kelayakan sarana prasarana.',
            ],
        ];

        foreach ($categories as $code => $category) {
            $parent = StandardCategory::updateOrCreate(
                ['code' => $code],
                [
                    'parent_id' => null,
                    'name' => $category['name'],
                    'description' => $category['description'],
                ],
            );

            if (! in_array($code, ['PDD', 'PNL', 'PKM'], true)) {
                continue;
            }

            foreach ($this->subcategories($code) as $subcategoryCode => $subcategoryName) {
                StandardCategory::updateOrCreate(
                    ['code' => $subcategoryCode],
                    [
                        'parent_id' => $parent->id,
                        'name' => $subcategoryName,
                        'description' => "Subkategori {$subcategoryName} untuk {$parent->name}.",
                    ],
                );
            }
        }
    }

    /**
     * @return array<string, string>
     */
    private function subcategories(string $parentCode): array
    {
        return [
            "{$parentCode}-MSK" => 'Masukan',
            "{$parentCode}-PRS" => 'Proses',
            "{$parentCode}-LRN" => 'Luaran',
        ];
    }
}
