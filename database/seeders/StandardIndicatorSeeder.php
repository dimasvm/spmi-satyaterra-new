<?php

namespace Database\Seeders;

use App\Models\QualityStandard;
use App\Models\StandardIndicator;
use Illuminate\Database\Seeder;

class StandardIndicatorSeeder extends Seeder
{
    /**
     * Seed example indicators for quality standards.
     */
    public function run(): void
    {
        $indicators = [
            'PDD-01' => [
                [
                    'code' => 'IKU-01',
                    'statement' => 'Persentase lulusan yang bekerja, berwirausaha, atau melanjutkan studi maksimal 6 bulan setelah lulus.',
                    'indicator_type' => 'percentage',
                    'target_value' => 80,
                    'target_operator' => '>=',
                    'target_unit' => '%',
                    'weight' => 5,
                    'evidence_description' => 'Tracer study, rekap alumni, dan bukti pendukung status lulusan.',
                ],
                [
                    'code' => 'IKU-02',
                    'statement' => 'Kesesuaian capaian pembelajaran lulusan dengan profil lulusan ditinjau minimal satu kali dalam satu tahun.',
                    'indicator_type' => 'number',
                    'target_value' => 1,
                    'target_operator' => '>=',
                    'target_unit' => 'kegiatan',
                    'weight' => 3,
                    'evidence_description' => 'Berita acara rapat kurikulum dan dokumen hasil peninjauan CPL.',
                ],
            ],
            'PDD-02' => [
                [
                    'code' => 'IKU-01',
                    'statement' => 'Persentase mata kuliah yang memiliki RPS terbaru dan tervalidasi sebelum perkuliahan dimulai.',
                    'indicator_type' => 'percentage',
                    'target_value' => 100,
                    'target_operator' => '=',
                    'target_unit' => '%',
                    'weight' => 5,
                    'evidence_description' => 'Dokumen RPS tervalidasi dan daftar mata kuliah aktif.',
                ],
                [
                    'code' => 'IKU-02',
                    'statement' => 'Persentase pelaksanaan perkuliahan sesuai rencana pembelajaran semester.',
                    'indicator_type' => 'percentage',
                    'target_value' => 90,
                    'target_operator' => '>=',
                    'target_unit' => '%',
                    'weight' => 4,
                    'evidence_description' => 'Jurnal perkuliahan, presensi, dan laporan monitoring pembelajaran.',
                ],
            ],
            'PNL-01' => [
                [
                    'code' => 'IKU-01',
                    'statement' => 'Jumlah publikasi ilmiah dosen pada jurnal atau prosiding bereputasi dalam satu tahun akademik.',
                    'indicator_type' => 'number',
                    'target_value' => 10,
                    'target_operator' => '>=',
                    'target_unit' => 'publikasi',
                    'weight' => 4,
                    'evidence_description' => 'Daftar publikasi, tautan artikel, atau surat penerimaan publikasi.',
                ],
            ],
            'PKM-01' => [
                [
                    'code' => 'IKU-01',
                    'statement' => 'Jumlah kegiatan pengabdian kepada masyarakat yang melibatkan dosen dan mahasiswa dalam satu tahun akademik.',
                    'indicator_type' => 'number',
                    'target_value' => 6,
                    'target_operator' => '>=',
                    'target_unit' => 'kegiatan',
                    'weight' => 4,
                    'evidence_description' => 'Proposal, laporan kegiatan, dokumentasi, dan surat keterangan mitra.',
                ],
            ],
        ];

        foreach ($indicators as $standardCode => $standardIndicators) {
            $standard = QualityStandard::where('code', $standardCode)->first();

            if (! $standard) {
                continue;
            }

            foreach ($standardIndicators as $indicator) {
                StandardIndicator::updateOrCreate(
                    [
                        'quality_standard_id' => $standard->id,
                        'code' => $indicator['code'],
                    ],
                    [
                        'statement' => $indicator['statement'],
                        'indicator_type' => $indicator['indicator_type'],
                        'target_value' => $indicator['target_value'],
                        'target_operator' => $indicator['target_operator'],
                        'target_unit' => $indicator['target_unit'],
                        'weight' => $indicator['weight'],
                        'evidence_required' => true,
                        'evidence_description' => $indicator['evidence_description'],
                    ],
                );
            }
        }
    }
}
