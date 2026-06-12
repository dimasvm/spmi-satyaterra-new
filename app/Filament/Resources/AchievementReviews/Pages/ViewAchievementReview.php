<?php

namespace App\Filament\Resources\AchievementReviews\Pages;

use App\Enums\AchievementReviewStatus;
use App\Enums\EvidenceFileType;
use App\Enums\IndicatorAssignmentStatus;
use App\Enums\SubmissionStatus;
use App\Filament\Resources\AchievementReviews\AchievementReviewResource;
use App\Filament\Resources\AchievementReviews\Tables\AchievementReviewsTable;
use App\Models\AchievementEvidence;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\URL;

class ViewAchievementReview extends ViewRecord
{
    protected static string $resource = AchievementReviewResource::class;

    protected string $view = 'filament.resources.achievement-reviews.pages.view-achievement-review';

    public function getTitle(): string|Htmlable
    {
        return 'Detail Validasi Capaian';
    }

    public function getSubheading(): string|Htmlable|null
    {
        $achievement = $this->getRecord()->achievement;
        $indicator = $achievement?->standard_indicator;
        $unit = $achievement?->assignment?->unit?->name;

        return trim(implode(' - ', array_filter([
            $indicator?->code,
            $unit,
        ]))) ?: null;
    }

    protected function getHeaderActions(): array
    {
        return [
            AchievementReviewsTable::reviewAction(
                name: 'validateAchievement',
                label: 'Validasi',
                reviewStatus: AchievementReviewStatus::Validated,
                submissionStatus: SubmissionStatus::Validated,
                assignmentStatus: IndicatorAssignmentStatus::Validated,
                color: 'success',
                icon: Heroicon::OutlinedCheckCircle,
                notesRequired: false,
            ),
            AchievementReviewsTable::reviewAction(
                name: 'returnAchievement',
                label: 'Kembalikan',
                reviewStatus: AchievementReviewStatus::Returned,
                submissionStatus: SubmissionStatus::Returned,
                assignmentStatus: IndicatorAssignmentStatus::Returned,
                color: 'warning',
                icon: Heroicon::OutlinedArrowUturnLeft,
                notesRequired: true,
            ),
            AchievementReviewsTable::reviewAction(
                name: 'rejectAchievement',
                label: 'Tolak',
                reviewStatus: AchievementReviewStatus::Rejected,
                submissionStatus: SubmissionStatus::Returned,
                assignmentStatus: IndicatorAssignmentStatus::Returned,
                color: 'danger',
                icon: Heroicon::OutlinedXCircle,
                notesRequired: true,
            ),
        ];
    }

    public function targetSummary(): string
    {
        $indicator = $this->getRecord()->achievement?->standard_indicator;

        if ($indicator === null) {
            return '-';
        }

        return trim(implode(' ', array_filter([
            $indicator->target_operator?->value,
            (float) $indicator->target_value,
            $indicator->target_unit,
        ]))) ?: '-';
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
}
