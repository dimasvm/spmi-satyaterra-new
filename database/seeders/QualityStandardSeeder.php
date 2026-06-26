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
                'statement' => 'Rektor, Dekan, dan Ketua Program Studi memastikan penetapan, pengukuran, pencapaian, evaluasi, dan pengendalian standar kompetensi lulusan untuk seluruh jenjang pendidikan di Universitas Pancasila.',
                'description' => 'Universitas Pancasila menetapkan kriteria minimum mengenai kesatuan kompetensi, sikap, keterampilan, dan pengetahuan, yang menunjukkan pencapaian mahasiswa pada pembelajaran program pendidikan tinggi.',
                'statements' => [
                    [
                        'code' => 'PDD-01-PS-01',
                        'statement' => 'Rektor memastikan tersedianya standar kompetensi lulusan yang dirumuskan dalam capaian pembelajaran lulusan program studi sesuai visi dan misi perguruan tinggi, visi keilmuan program studi, jenjang KKNI, profil lulusan, perkembangan IPTEK dan/atau kebutuhan pengguna (dunia usaha, dunia industri, dunia kerja) yang terdiri dari sikap, penguasaan pengetahuan, keterampilan umum, keterampilan khusus serta diperbaharui sesuai peraturan perundang-undangan/peraturan pemerintah/peraturan menteri.',
                        'sort_order' => 1,
                    ],
                    [
                        'code' => 'PDD-01-PS-02',
                        'statement' => 'Rektor memastikan dilakukannya pengukuran terhadap pencapaian standar kompetensi lulusan yang mencakup kesatuan kompetensi sikap, keterampilan, dan pengetahuan setiap tahun akademik.',
                        'sort_order' => 2,
                    ],
                    [
                        'code' => 'PDD-01-PS-03',
                        'statement' => 'Rektor memastikan bahwa setiap lulusan UP memiliki kemampuan Bahasa Inggris dengan nilai TOEFL 400 untuk Program Diploma Tiga/Sarjana Terapan/Sarjana, nilai TOEFL 450 untuk program Magister/Profesi, dan nilai TOEFL 475 untuk program Doktor/Doktor Terapan sebelum menyelesaikan masa studi.',
                        'sort_order' => 3,
                    ],
                    [
                        'code' => 'PDD-01-PS-04',
                        'statement' => 'Rektor memastikan bahwa setiap lulusan Program Studi Diploma Tiga/Sarjana Terapan/Sarjana memiliki sertifikat kompetensi tingkat nasional maupun internasional sekurang-kurangnya 1 (satu) sertifikat sesuai bidang keahlian yang diperoleh sebelum menyelesaikan masa studi.',
                        'sort_order' => 4,
                    ],
                    [
                        'code' => 'PDD-01-PS-05',
                        'statement' => 'Rektor memastikan tersedianya kegiatan tracer study dilakukan di tingkat Universitas dengan melibatkan Unit Pengelola Program Studi (UPPS) dan Program Studi serta dilakukan secara berkala setiap tahun.',
                        'sort_order' => 5,
                    ],
                    [
                        'code' => 'PDD-01-PS-06',
                        'statement' => 'Dekan memastikan tersedianya profil lulusan dan CPL seluruh program studi sesuai dengan KKNI, perkembangan IPTEK, dan kebutuhan pengguna (dunia usaha, dunia industri, dunia kerja) yang telah dievaluasi setiap 4 – 5 tahun.',
                        'sort_order' => 6,
                    ],
                    [
                        'code' => 'PDD-01-PS-07',
                        'statement' => 'Dekan memastikan proses pembelajaran berjalan sesuai pedoman akademik yang telah ditetapkan dan dievaluasi setiap tahun.',
                        'sort_order' => 7,
                    ],
                    [
                        'code' => 'PDD-01-PS-08',
                        'statement' => 'Dekan memastikan tersedianya informasi jadwal pelaksanaan asesmen TOEFL yang dapat diikuti oleh seluruh mahasiswa di lingkungan UPPS setiap semester.',
                        'sort_order' => 8,
                    ],
                    [
                        'code' => 'PDD-01-PS-09',
                        'statement' => 'Dekan memastikan tersedianya informasi jadwal pelaksanaan sertifikasi kompetensi yang terkoordinasi di Universitas sesuai dengan keilmuan program studi setiap semester.',
                        'sort_order' => 9,
                    ],
                    [
                        'code' => 'PDD-01-PS-10',
                        'statement' => 'Ketua Program Studi memastikan tersedianya profil lulusan dan CPL program studi yang dirumuskan berdasarkan KKNI dan perkembangan IPTEK serta hasil tukar pikiran dengan pemangku kepentingan internal dan pemangku kepentingan eksternal kemudian dievaluasi setiap 4 – 5 tahun.',
                        'sort_order' => 10,
                    ],
                    [
                        'code' => 'PDD-01-PS-11',
                        'statement' => 'Ketua Program Studi memastikan masa studi mahasiswa sesuai dengan masa tempuh kurikulum yang telah ditetapkan.',
                        'sort_order' => 11,
                    ],
                    [
                        'code' => 'PDD-01-PS-12',
                        'statement' => 'Ketua Program Studi memastikan seluruh mahasiswa telah memiliki sertifikat TOEFL sesuai ketetapan Universitas dan sertifikat kompetensi sekurang-kurangnya 1 (satu) sertifikat serta ditunjukkan sebagai syarat pengambilan ijazah dan transkrip nilai.',
                        'sort_order' => 12,
                    ],
                    [
                        'code' => 'PDD-01-PS-13',
                        'statement' => 'Ketua Program Studi memastikan tersedianya Dosen yang membimbing mahasiswa mendapatkan sertifikat internasional setiap semester.',
                        'sort_order' => 13,
                    ],
                ],
            ],
        ];

        foreach ($standards as $standard) {
            $category = StandardCategory::where('code', $standard['category_code'])->first();

            if (! $category || ! $period) {
                continue;
            }

            $standardModel = QualityStandard::updateOrCreate(
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

            $seededStatementCodes = [];
            foreach ($standard['statements'] as $stmt) {
                StandardStatement::updateOrCreate(
                    [
                        'quality_standard_id' => $standardModel->id,
                        'code' => $stmt['code'],
                    ],
                    [
                        'statement' => $stmt['statement'],
                        'sort_order' => $stmt['sort_order'],
                    ],
                );
                $seededStatementCodes[] = $stmt['code'];
            }

            // Cleanup obsolete statements for this standard
            StandardStatement::where('quality_standard_id', $standardModel->id)
                ->whereNotIn('code', $seededStatementCodes)
                ->delete();
        }

        // Cleanup obsolete standards
        if ($period) {
            $seededStandardCodes = collect($standards)->pluck('code')->toArray();
            QualityStandard::where('spmi_period_id', $period->id)
                ->whereNotIn('code', $seededStandardCodes)
                ->delete();
        }
    }
}
