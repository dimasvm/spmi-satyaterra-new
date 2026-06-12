<?php

namespace Database\Seeders;

use App\Enums\StandardIndicatorType;
use App\Enums\TargetOperator;
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
        QualityStandard::query()
            ->orderBy('code')
            ->get()
            ->each(function (QualityStandard $standard): void {
                foreach (range(1, 20) as $number) {
                    $indicator = $this->indicatorPayload($standard, $number);

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
                            'evidence_required' => $indicator['evidence_required'],
                            'evidence_description' => $indicator['evidence_description'],
                        ],
                    );
                }
            });
    }

    /**
     * @return array<string, mixed>
     */
    private function indicatorPayload(QualityStandard $standard, int $number): array
    {
        $types = [
            StandardIndicatorType::Percentage,
            StandardIndicatorType::Number,
            StandardIndicatorType::Checklist,
            StandardIndicatorType::Boolean,
            StandardIndicatorType::Text,
        ];

        $type = $types[($number - 1) % count($types)];
        $targetValue = match ($type) {
            StandardIndicatorType::Percentage => 70 + (($number * 3) % 26),
            StandardIndicatorType::Number => 1 + (($number * 2) % 12),
            StandardIndicatorType::Checklist, StandardIndicatorType::Boolean => 1,
            StandardIndicatorType::Text => 1,
        };
        $targetUnit = match ($type) {
            StandardIndicatorType::Percentage => '%',
            StandardIndicatorType::Number => $number % 2 === 0 ? 'dokumen' : 'kegiatan',
            StandardIndicatorType::Checklist => 'checklist',
            StandardIndicatorType::Boolean => 'ya/tidak',
            StandardIndicatorType::Text => 'narasi',
        };

        return [
            'code' => sprintf('%s-IKU-%02d', $standard->code, $number),
            'statement' => sprintf(
                'Indikator %02d untuk %s: unit memiliki bukti pelaksanaan, monitoring, evaluasi, dan tindak lanjut yang terdokumentasi.',
                $number,
                $standard->name,
            ),
            'indicator_type' => $type->value,
            'target_value' => $targetValue,
            'target_operator' => $number % 4 === 0 ? TargetOperator::Equal->value : TargetOperator::GreaterThanOrEqual->value,
            'target_unit' => $targetUnit,
            'weight' => 1 + ($number % 5),
            'evidence_required' => $number % 5 !== 0,
            'evidence_description' => sprintf(
                'Unggah dokumen pendukung indikator %02d, seperti SK, laporan, rekap, berita acara, foto kegiatan, atau tautan folder bukti.',
                $number,
            ),
        ];
    }
}
