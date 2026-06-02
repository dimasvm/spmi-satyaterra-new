<?php

namespace Database\Seeders;

use App\Models\QualityStandard;
use App\Models\SpmiPeriod;
use App\Models\StandardCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class QualityStandardSeeder extends Seeder
{
    /**
     * Seed example quality standards.
     */
    public function run(): void
    {
        $period = SpmiPeriod::where('status', 'active')->first();
        $approver = User::role('super_admin')->first();

        $standards = [
            [
                'category_code' => 'PDD',
                'code' => 'PDD-01',
                'name' => 'Standar Kompetensi Lulusan',
                'description' => 'Setiap program studi menetapkan capaian pembelajaran lulusan yang selaras dengan profil lulusan, kebutuhan pemangku kepentingan, dan ketentuan nasional.',
            ],
            [
                'category_code' => 'PDD',
                'code' => 'PDD-02',
                'name' => 'Standar Proses Pembelajaran',
                'description' => 'Setiap program studi melaksanakan proses pembelajaran yang interaktif, holistik, integratif, saintifik, kontekstual, tematik, efektif, kolaboratif, dan berpusat pada mahasiswa.',
            ],
            [
                'category_code' => 'PNL',
                'code' => 'PNL-01',
                'name' => 'Standar Hasil Penelitian',
                'description' => 'Setiap dosen menghasilkan luaran penelitian yang relevan dengan peta jalan penelitian institusi dan kebutuhan masyarakat.',
            ],
            [
                'category_code' => 'PKM',
                'code' => 'PKM-01',
                'name' => 'Standar Hasil Pengabdian kepada Masyarakat',
                'description' => 'Kegiatan pengabdian kepada masyarakat menghasilkan manfaat terukur bagi mitra dan terdokumentasi dalam laporan kegiatan.',
            ],
        ];

        foreach ($standards as $standard) {
            $category = StandardCategory::where('code', $standard['category_code'])->first();

            if (! $category || ! $period) {
                continue;
            }

            QualityStandard::updateOrCreate(
                [
                    'spmi_period_id' => $period->id,
                    'code' => $standard['code'],
                ],
                [
                    'standard_category_id' => $category->id,
                    'name' => $standard['name'],
                    'description' => $standard['description'],
                    'status' => 'active',
                    'version' => 1,
                    'approved_at' => now(),
                    'approved_by' => $approver?->id,
                ],
            );
        }
    }
}
