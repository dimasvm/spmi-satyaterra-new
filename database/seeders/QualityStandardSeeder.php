<?php

namespace Database\Seeders;

use App\Enums\QualityStandardStatus;
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
        $approver = User::role('admin_lpm')->first() ?? User::role('super_admin')->first();

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
                'category_code' => 'PDD',
                'code' => 'PDD-03',
                'name' => 'Standar Penilaian Pembelajaran',
                'description' => 'Setiap program studi melaksanakan penilaian pembelajaran yang transparan, objektif, akuntabel, dan terdokumentasi.',
            ],
            [
                'category_code' => 'PNL',
                'code' => 'PNL-01',
                'name' => 'Standar Hasil Penelitian',
                'description' => 'Setiap dosen menghasilkan luaran penelitian yang relevan dengan peta jalan penelitian institusi dan kebutuhan masyarakat.',
            ],
            [
                'category_code' => 'PNL',
                'code' => 'PNL-02',
                'name' => 'Standar Proses Penelitian',
                'description' => 'Setiap kegiatan penelitian direncanakan, dilaksanakan, dipantau, dan dievaluasi sesuai peta jalan penelitian.',
            ],
            [
                'category_code' => 'PKM',
                'code' => 'PKM-01',
                'name' => 'Standar Hasil Pengabdian kepada Masyarakat',
                'description' => 'Kegiatan pengabdian kepada masyarakat menghasilkan manfaat terukur bagi mitra dan terdokumentasi dalam laporan kegiatan.',
            ],
            [
                'category_code' => 'PKM',
                'code' => 'PKM-02',
                'name' => 'Standar Proses Pengabdian kepada Masyarakat',
                'description' => 'Kegiatan pengabdian kepada masyarakat dilaksanakan berdasarkan kebutuhan mitra, kompetensi pelaksana, dan rencana kegiatan yang terukur.',
            ],
            [
                'category_code' => 'TKP',
                'code' => 'TKP-01',
                'name' => 'Standar Tata Pamong dan Tata Kelola',
                'description' => 'Unit kerja menerapkan tata pamong yang kredibel, transparan, akuntabel, bertanggung jawab, dan adil.',
            ],
            [
                'category_code' => 'SDM',
                'code' => 'SDM-01',
                'name' => 'Standar Dosen dan Tenaga Kependidikan',
                'description' => 'Unit memastikan kecukupan, kualifikasi, kompetensi, dan pengembangan dosen serta tenaga kependidikan.',
            ],
            [
                'category_code' => 'SAR',
                'code' => 'SAR-01',
                'name' => 'Standar Sarana dan Prasarana',
                'description' => 'Unit menyediakan sarana dan prasarana yang layak, aman, mudah diakses, dan mendukung tridharma perguruan tinggi.',
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
                    'status' => QualityStandardStatus::Active->value,
                    'version' => 1,
                    'approved_at' => now(),
                    'approved_by' => $approver?->id,
                ],
            );
        }
    }
}
