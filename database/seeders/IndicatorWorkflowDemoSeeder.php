<?php

namespace Database\Seeders;

use App\Enums\AchievementReviewStatus;
use App\Enums\AchievementStatus;
use App\Enums\EvidenceFileType;
use App\Enums\IndicatorAssignmentPriority;
use App\Enums\IndicatorAssignmentStatus;
use App\Enums\SubmissionStatus;
use App\Models\AchievementEvidence;
use App\Models\AchievementReview;
use App\Models\IndicatorAchievement;
use App\Models\IndicatorUnitAssignment;
use App\Models\QualityStandard;
use App\Models\SpmiPeriod;
use App\Models\StandardIndicator;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;

class IndicatorWorkflowDemoSeeder extends Seeder
{
    /**
     * Seed indicator assignments, achievements, evidences, and LPM reviews.
     */
    public function run(): void
    {
        $period = SpmiPeriod::query()->where('status', 'active')->first();
        $assigner = User::role('admin_lpm')->first() ?? User::role('super_admin')->first();
        $reviewer = User::role('admin_lpm')->skip(1)->first() ?? $assigner;
        $units = Unit::query()->orderBy('code')->get();

        if ($period === null || $assigner === null || $units->isEmpty()) {
            return;
        }

        QualityStandard::query()
            ->with(['indicators' => fn ($query) => $query->orderBy('code')->limit(5)])
            ->orderBy('code')
            ->get()
            ->each(function (QualityStandard $standard) use ($period, $assigner, $reviewer, $units): void {
                foreach ($standard->indicators as $indicatorIndex => $indicator) {
                    foreach ($units as $unitIndex => $unit) {
                        $assignment = IndicatorUnitAssignment::updateOrCreate(
                            [
                                'standard_indicator_id' => $indicator->id,
                                'unit_id' => $unit->id,
                                'spmi_period_id' => $period->id,
                            ],
                            [
                                'due_date' => now()->addDays(30 + $indicatorIndex + $unitIndex)->toDateString(),
                                'status' => $this->assignmentStatus($unitIndex),
                                'is_primary_pic' => true,
                                'priority' => $this->priority($indicatorIndex)->value,
                                'notes' => 'Penugasan demo untuk '.$indicator->code.' kepada '.$unit->name.'.',
                                'assigned_by' => $assigner->id,
                                'assigned_at' => now()->subDays(20),
                            ],
                        );

                        $this->seedAchievement($assignment, $indicator, $unitIndex, $reviewer);
                    }
                }
            });
    }

    private function seedAchievement(
        IndicatorUnitAssignment $assignment,
        StandardIndicator $indicator,
        int $unitIndex,
        ?User $reviewer,
    ): void {
        if ($unitIndex > 5) {
            return;
        }

        $submissionStatus = match (true) {
            $unitIndex <= 2 => SubmissionStatus::Submitted,
            $unitIndex <= 4 => SubmissionStatus::Validated,
            default => SubmissionStatus::Returned,
        };

        $achievementStatus = $unitIndex % 3 === 0
            ? AchievementStatus::Achieved
            : AchievementStatus::PartiallyAchieved;

        $achievement = IndicatorAchievement::updateOrCreate(
            ['assignment_id' => $assignment->id],
            [
                'realization_value' => $this->realizationValue($indicator, $achievementStatus),
                'realization_text' => 'Unit telah melaksanakan aktivitas terkait '.$indicator->code.' dan mengunggah bukti pendukung untuk proses validasi.',
                'achievement_status' => $achievementStatus->value,
                'notes' => 'Catatan unit untuk capaian '.$indicator->code.'.',
                'submission_status' => $submissionStatus->value,
                'submitted_at' => now()->subDays(8 - min($unitIndex, 5)),
                'submitted_by' => User::role('unit_pic')->where('unit_id', $assignment->unit_id)->first()?->id,
            ],
        );

        AchievementEvidence::updateOrCreate(
            [
                'indicator_achievement_id' => $achievement->id,
                'description' => 'Bukti utama '.$indicator->code,
            ],
            [
                'file_name' => null,
                'file_path' => null,
                'file_type' => EvidenceFileType::Link->value,
                'external_url' => sprintf('https://example.test/bukti-capaian/%s/%s', $assignment->unit_id, strtolower($indicator->code)),
                'uploaded_by' => $achievement->submitted_by,
            ],
        );

        if ($submissionStatus === SubmissionStatus::Submitted) {
            AchievementReview::updateOrCreate(
                [
                    'indicator_achievement_id' => $achievement->id,
                    'status' => AchievementReviewStatus::Pending->value,
                ],
                [
                    'reviewer_id' => null,
                    'notes' => null,
                    'reviewed_at' => null,
                ],
            );

            return;
        }

        AchievementReview::updateOrCreate(
            [
                'indicator_achievement_id' => $achievement->id,
                'status' => $submissionStatus === SubmissionStatus::Validated
                    ? AchievementReviewStatus::Validated->value
                    : AchievementReviewStatus::Returned->value,
            ],
            [
                'reviewer_id' => $reviewer?->id,
                'notes' => $submissionStatus === SubmissionStatus::Validated
                    ? 'Capaian dan bukti sudah sesuai.'
                    : 'Mohon lengkapi bukti dan narasi realisasi.',
                'reviewed_at' => now()->subDays(2),
            ],
        );
    }

    private function assignmentStatus(int $unitIndex): string
    {
        return match (true) {
            $unitIndex <= 2 => IndicatorAssignmentStatus::Submitted->value,
            $unitIndex <= 4 => IndicatorAssignmentStatus::Validated->value,
            $unitIndex === 5 => IndicatorAssignmentStatus::Returned->value,
            default => IndicatorAssignmentStatus::Assigned->value,
        };
    }

    private function priority(int $indicatorIndex): IndicatorAssignmentPriority
    {
        return match ($indicatorIndex % 3) {
            0 => IndicatorAssignmentPriority::High,
            1 => IndicatorAssignmentPriority::Normal,
            default => IndicatorAssignmentPriority::Low,
        };
    }

    private function realizationValue(StandardIndicator $indicator, AchievementStatus $status): float
    {
        $targetValue = (float) $indicator->target_value;

        if ($targetValue <= 0) {
            return 1;
        }

        return $status === AchievementStatus::Achieved
            ? $targetValue
            : max(1, round($targetValue * 0.75, 2));
    }
}
