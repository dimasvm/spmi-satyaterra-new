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
        $uploader = User::role('admin_lpm')->first() ?? User::role('super_admin')->first();

        QualityStandard::query()
            ->with('spmiPeriod')
            ->orderBy('code')
            ->get()
            ->each(function (QualityStandard $standard) use ($uploader): void {
                foreach ($this->documentTypes() as $index => $type) {
                    QualityDocument::updateOrCreate(
                        [
                            'quality_standard_id' => $standard->id,
                            'document_number' => sprintf('%s-DOK-%02d', $standard->code, $index + 1),
                        ],
                        [
                            'spmi_period_id' => $standard->spmi_period_id,
                            'title' => $this->documentTitle($standard, $type),
                            'document_type' => $type->value,
                            'version' => 1,
                            'file_path' => null,
                            'external_url' => sprintf('https://example.test/dokumen-mutu/%s/%s', strtolower($standard->code), $type->value),
                            'status' => QualityDocumentStatus::Active->value,
                            'uploaded_by' => $uploader?->id,
                            'approved_by' => $uploader?->id,
                            'approved_at' => now()->subDays(10 - $index),
                        ],
                    );
                }
            });
    }

    /**
     * @return array<int, QualityDocumentType>
     */
    private function documentTypes(): array
    {
        return [
            QualityDocumentType::Standard,
            QualityDocumentType::Sop,
            QualityDocumentType::Form,
            QualityDocumentType::Guideline,
        ];
    }

    private function documentTitle(QualityStandard $standard, QualityDocumentType $type): string
    {
        return match ($type) {
            QualityDocumentType::Standard => 'Dokumen Standar '.$standard->name,
            QualityDocumentType::Sop => 'SOP Pelaksanaan '.$standard->name,
            QualityDocumentType::Form => 'Formulir Monitoring '.$standard->name,
            QualityDocumentType::Guideline => 'Pedoman Evaluasi '.$standard->name,
            default => 'Dokumen Mutu '.$standard->name,
        };
    }
}
