<?php

namespace Database\Seeders;

use App\Enums\QualityStandardStatus;
use App\Enums\UnitType;
use App\Models\QualityStandard;
use App\Models\SpmiPeriod;
use App\Models\StandardCategory;
use App\Models\StandardStatement;
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
                'category_code' => 'PDD-LRN',
                'scope_type' => UnitType::StudyProgram,
                'code' => 'PDD-01',
                'name' => 'Standar Kompetensi Lulusan',
                'statement' => 'Program studi menetapkan capaian pembelajaran lulusan yang selaras dengan profil lulusan, kebutuhan pemangku kepentingan, dan ketentuan nasional.',
                'description' => 'Setiap program studi menetapkan capaian pembelajaran lulusan yang selaras dengan profil lulusan, kebutuhan pemangku kepentingan, dan ketentuan nasional.',
            ],
            [
                'category_code' => 'PDD-PRS',
                'scope_type' => UnitType::StudyProgram,
                'code' => 'PDD-02',
                'name' => 'Standar Proses Pembelajaran',
                'statement' => 'Program studi melaksanakan proses pembelajaran yang interaktif, holistik, integratif, saintifik, kontekstual, efektif, kolaboratif, dan berpusat pada mahasiswa.',
                'description' => 'Setiap program studi melaksanakan proses pembelajaran yang interaktif, holistik, integratif, saintifik, kontekstual, tematik, efektif, kolaboratif, dan berpusat pada mahasiswa.',
            ],
            [
                'category_code' => 'PDD-PRS',
                'scope_type' => UnitType::StudyProgram,
                'code' => 'PDD-03',
                'name' => 'Standar Penilaian Pembelajaran',
                'statement' => 'Program studi melaksanakan penilaian pembelajaran yang transparan, objektif, akuntabel, dan terdokumentasi.',
                'description' => 'Setiap program studi melaksanakan penilaian pembelajaran yang transparan, objektif, akuntabel, dan terdokumentasi.',
            ],
            [
                'category_code' => 'PNL-LRN',
                'scope_type' => UnitType::StudyProgram,
                'code' => 'PNL-01',
                'name' => 'Standar Hasil Penelitian',
                'statement' => 'Dosen menghasilkan luaran penelitian yang relevan dengan peta jalan penelitian institusi dan kebutuhan masyarakat.',
                'description' => 'Setiap dosen menghasilkan luaran penelitian yang relevan dengan peta jalan penelitian institusi dan kebutuhan masyarakat.',
            ],
            [
                'category_code' => 'PNL-PRS',
                'scope_type' => UnitType::StudyProgram,
                'code' => 'PNL-02',
                'name' => 'Standar Proses Penelitian',
                'statement' => 'Kegiatan penelitian direncanakan, dilaksanakan, dipantau, dan dievaluasi sesuai peta jalan penelitian.',
                'description' => 'Setiap kegiatan penelitian direncanakan, dilaksanakan, dipantau, dan dievaluasi sesuai peta jalan penelitian.',
            ],
            [
                'category_code' => 'PKM-LRN',
                'scope_type' => UnitType::StudyProgram,
                'code' => 'PKM-01',
                'name' => 'Standar Hasil Pengabdian kepada Masyarakat',
                'statement' => 'Kegiatan pengabdian kepada masyarakat menghasilkan manfaat terukur bagi mitra dan terdokumentasi dalam laporan kegiatan.',
                'description' => 'Kegiatan pengabdian kepada masyarakat menghasilkan manfaat terukur bagi mitra dan terdokumentasi dalam laporan kegiatan.',
            ],
            [
                'category_code' => 'PKM-PRS',
                'scope_type' => UnitType::StudyProgram,
                'code' => 'PKM-02',
                'name' => 'Standar Proses Pengabdian kepada Masyarakat',
                'statement' => 'Kegiatan pengabdian kepada masyarakat dilaksanakan berdasarkan kebutuhan mitra, kompetensi pelaksana, dan rencana kegiatan yang terukur.',
                'description' => 'Kegiatan pengabdian kepada masyarakat dilaksanakan berdasarkan kebutuhan mitra, kompetensi pelaksana, dan rencana kegiatan yang terukur.',
            ],
            [
                'category_code' => 'TKP',
                'scope_type' => UnitType::University,
                'code' => 'TKP-01',
                'name' => 'Standar Tata Pamong dan Tata Kelola',
                'statement' => 'Unit kerja menerapkan tata pamong yang kredibel, transparan, akuntabel, bertanggung jawab, dan adil.',
                'description' => 'Unit kerja menerapkan tata pamong yang kredibel, transparan, akuntabel, bertanggung jawab, dan adil.',
            ],
            [
                'category_code' => 'SDM',
                'scope_type' => UnitType::University,
                'code' => 'SDM-01',
                'name' => 'Standar Dosen dan Tenaga Kependidikan',
                'statement' => 'Unit memastikan kecukupan, kualifikasi, kompetensi, dan pengembangan dosen serta tenaga kependidikan.',
                'description' => 'Unit memastikan kecukupan, kualifikasi, kompetensi, dan pengembangan dosen serta tenaga kependidikan.',
            ],
            [
                'category_code' => 'SAR',
                'scope_type' => UnitType::University,
                'code' => 'SAR-01',
                'name' => 'Standar Sarana dan Prasarana',
                'statement' => 'Unit menyediakan sarana dan prasarana yang layak, aman, mudah diakses, dan mendukung tridharma perguruan tinggi.',
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
                    'scope_type' => $standard['scope_type']->value,
                    'name' => $standard['name'],
                    'statement' => $standard['statement'],
                    'description' => $standard['description'],
                    'status' => QualityStandardStatus::Active->value,
                    'version' => 1,
                    'approved_at' => now(),
                    'approved_by' => $approver?->id,
                ],
            );

            $standardModel = QualityStandard::query()
                ->where('spmi_period_id', $period->id)
                ->where('code', $standard['code'])
                ->first();

            if ($standardModel === null) {
                continue;
            }

            StandardStatement::updateOrCreate(
                [
                    'quality_standard_id' => $standardModel->id,
                    'code' => 'PS-001',
                ],
                [
                    'statement' => $standard['statement'],
                    'sort_order' => 1,
                ],
            );
        }
    }
}
