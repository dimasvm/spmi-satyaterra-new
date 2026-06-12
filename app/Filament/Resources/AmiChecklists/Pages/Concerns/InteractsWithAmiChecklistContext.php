<?php

namespace App\Filament\Resources\AmiChecklists\Pages\Concerns;

use App\Enums\EvidenceFileType;
use App\Models\AchievementEvidence;
use App\Models\AchievementReview;
use App\Models\IndicatorAchievement;
use App\Models\IndicatorUnitAssignment;
use Illuminate\Support\Facades\URL;

trait InteractsWithAmiChecklistContext
{
    protected ?IndicatorUnitAssignment $unitAssignmentCache = null;

    public function targetSummary(): string
    {
        $indicator = $this->getRecord()->standardIndicator;

        if ($indicator === null) {
            return '-';
        }

        return trim(implode(' ', array_filter([
            $indicator->target_operator?->value,
            (float) $indicator->target_value,
            $indicator->target_unit,
        ]))) ?: '-';
    }

    public function unitAssignment(): ?IndicatorUnitAssignment
    {
        if ($this->unitAssignmentCache instanceof IndicatorUnitAssignment) {
            return $this->unitAssignmentCache;
        }

        $audit = $this->getRecord()->audit;
        $spmiPeriodId = $audit?->amiPeriod?->spmi_period_id;

        if ($audit === null || $spmiPeriodId === null) {
            return null;
        }

        $this->unitAssignmentCache = IndicatorUnitAssignment::query()
            ->with([
                'latestAchievement.evidences',
                'latestAchievement.latestReview.reviewer',
                'latestAchievement.reviews.reviewer',
            ])
            ->where('unit_id', $audit->auditee_unit_id)
            ->where('spmi_period_id', $spmiPeriodId)
            ->where('standard_indicator_id', $this->getRecord()->standard_indicator_id)
            ->first();

        return $this->unitAssignmentCache;
    }

    public function unitAchievement(): ?IndicatorAchievement
    {
        return $this->unitAssignment()?->latestAchievement;
    }

    public function latestFinalReview(): ?AchievementReview
    {
        return $this->unitAchievement()
            ?->reviews
            ->whereNotNull('reviewed_at')
            ->sortByDesc('reviewed_at')
            ->first();
    }

    public function evidenceUrl(AchievementEvidence $evidence): ?string
    {
        if ($evidence->file_type === EvidenceFileType::Link) {
            return $evidence->external_url;
        }

        if (blank($evidence->file_path)) {
            return null;
        }

        return URL::signedRoute('achievement-evidences.show', $evidence, absolute: false);
    }

    public function evidencePreviewType(AchievementEvidence $evidence): string
    {
        return match ($evidence->file_type) {
            EvidenceFileType::Image => 'image',
            EvidenceFileType::Pdf => 'pdf',
            EvidenceFileType::Link => 'link',
            default => 'file',
        };
    }

    public function evidenceName(AchievementEvidence $evidence): string
    {
        return $evidence->file_name
            ?: $evidence->external_url
            ?: basename((string) $evidence->file_path)
            ?: 'Bukti capaian';
    }

    public function canAssessChecklist(): bool
    {
        return auth()->user()?->can('update', $this->getRecord()) ?? false;
    }
}
