<?php

namespace Database\Seeders;

use App\Enums\QualityDocumentStatus;
use App\Enums\QualityDocumentType;
use App\Models\QualityDocument;
use App\Models\QualityStandard;
use App\Models\User;
use Illuminate\Database\Seeder;

class QualityDocumentDemoSeeder extends Seeder
{
    /**
     * Seed active quality documents for each standard.
     */
    public function run(): void
    {
        $documents = [
            'PDD-01' => [
                [
                    'title' => 'Buku Panduan Kurikulum Berbasis Kompetensi',
                    'document_type' => QualityDocumentType::Guideline,
                    'document_number' => 'KB-1-1.1-0109-15-01',
                    'external_url' => 'https://example.test/dokumen-mutu/pdd-01/buku-panduan-kbk',
                ],
                [
                    'title' => 'Panduan Penyusunan Kurikulum Pendidikan Tinggi',
                    'document_type' => QualityDocumentType::Guideline,
                    'document_number' => 'KB-1-1.1-0109-15-02',
                    'external_url' => 'https://example.test/dokumen-mutu/pdd-01/panduan-penyusunan-kurikulum-pt',
                ],
                [
                    'title' => 'Pedoman Pengembangan Kurikulum 2023',
                    'document_type' => QualityDocumentType::Guideline,
                    'document_number' => 'KB-1-1.1-0109-15-03',
                    'external_url' => 'https://example.test/dokumen-mutu/pdd-01/pedoman-pengembangan-kurikulum-2023',
                ],
                [
                    'title' => 'Pedoman Surat Keterangan Pendamping Ijazah',
                    'document_type' => QualityDocumentType::Guideline,
                    'document_number' => 'KB-1-1.1-0109-15-04',
                    'external_url' => 'https://example.test/dokumen-mutu/pdd-01/pedoman-skpi',
                ],
                [
                    'title' => 'SOP Pengambilan Ijazah dan Transkrip Nilai',
                    'document_type' => QualityDocumentType::Sop,
                    'document_number' => 'KB-1-1.1-0109-15-05',
                    'external_url' => 'https://example.test/dokumen-mutu/pdd-01/sop-pengambilan-ijazah-transkrip',
                ],
                [
                    'title' => 'SOP Tracer Study',
                    'document_type' => QualityDocumentType::Sop,
                    'document_number' => 'KB-1-1.1-0109-15-06',
                    'external_url' => 'https://example.test/dokumen-mutu/pdd-01/sop-tracer-study',
                ],
                [
                    'title' => 'SOP Survey Pengguna Lulusan',
                    'document_type' => QualityDocumentType::Sop,
                    'document_number' => 'KB-1-1.1-0109-15-07',
                    'external_url' => 'https://example.test/dokumen-mutu/pdd-01/sop-survey-pengguna-lulusan',
                ],
            ],
        ];

        $uploader = User::role('admin_lpm')->first() ?? User::role('super_admin')->first();

        foreach ($documents as $standardCode => $docList) {
            $standard = QualityStandard::where('code', $standardCode)->first();
            if ($standard === null) {
                continue;
            }

            $seededDocNumbers = [];
            foreach ($docList as $index => $doc) {
                QualityDocument::updateOrCreate(
                    [
                        'quality_standard_id' => $standard->id,
                        'document_number' => $doc['document_number'],
                    ],
                    [
                        'spmi_period_id' => $standard->spmi_period_id,
                        'title' => $doc['title'],
                        'document_type' => $doc['document_type']->value,
                        'version' => 1,
                        'file_path' => null,
                        'external_url' => $doc['external_url'],
                        'status' => QualityDocumentStatus::Active->value,
                        'uploaded_by' => $uploader?->id,
                        'approved_by' => $uploader?->id,
                        'approved_at' => now()->subDays(10 - $index),
                    ]
                );
                $seededDocNumbers[] = $doc['document_number'];
            }

            // Cleanup documents not in this seeder list for this standard
            QualityDocument::where('quality_standard_id', $standard->id)
                ->whereNotIn('document_number', $seededDocNumbers)
                ->delete();
        }
    }
}
