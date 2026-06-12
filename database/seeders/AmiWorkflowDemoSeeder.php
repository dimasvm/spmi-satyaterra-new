<?php

namespace Database\Seeders;

use App\Enums\AmiAssessmentResult;
use App\Enums\AmiAuditorRole;
use App\Enums\AmiAuditStatus;
use App\Enums\AmiFindingCategory;
use App\Enums\AmiFindingStatus;
use App\Enums\AmiPeriodStatus;
use App\Enums\CorrectiveActionReviewStatus;
use App\Enums\CorrectiveActionStatus;
use App\Models\AmiAudit;
use App\Models\AmiAuditor;
use App\Models\AmiChecklist;
use App\Models\AmiFinding;
use App\Models\AmiPeriod;
use App\Models\CorrectiveAction;
use App\Models\CorrectiveActionEvidence;
use App\Models\CorrectiveActionReview;
use App\Models\IndicatorUnitAssignment;
use App\Models\SpmiPeriod;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;

class AmiWorkflowDemoSeeder extends Seeder
{
    /**
     * Seed AMI audits, checklist results, findings, and corrective actions.
     */
    public function run(): void
    {
        $spmiPeriod = SpmiPeriod::query()->where('status', 'active')->first();
        $admin = User::role('admin_lpm')->first() ?? User::role('super_admin')->first();
        $auditors = User::role('auditor')->orderBy('email')->get()->values();
        $units = Unit::query()->whereIn('code', ['TI', 'SI', 'MNJ', 'AKT', 'PGSD'])->orderBy('code')->get()->values();

        if ($spmiPeriod === null || $admin === null || $auditors->isEmpty() || $units->isEmpty()) {
            return;
        }

        $amiPeriod = AmiPeriod::updateOrCreate(
            [
                'spmi_period_id' => $spmiPeriod->id,
                'name' => 'AMI '.$spmiPeriod->name,
            ],
            [
                'start_date' => now()->subDays(15)->toDateString(),
                'end_date' => now()->addDays(30)->toDateString(),
                'status' => AmiPeriodStatus::Ongoing->value,
            ],
        );

        foreach ($units as $index => $unit) {
            $audit = AmiAudit::updateOrCreate(
                [
                    'ami_period_id' => $amiPeriod->id,
                    'auditee_unit_id' => $unit->id,
                ],
                [
                    'scheduled_date' => now()->subDays(5 - $index)->toDateString(),
                    'status' => AmiAuditStatus::Finalized->value,
                    'notes' => 'Audit mutu internal demo untuk '.$unit->name.'.',
                    'finalized_at' => now()->subDays(1),
                    'finalized_by' => $admin->id,
                ],
            );

            $this->seedAuditors($audit, $auditors, $index);
            $this->seedChecklistsAndFindings($audit, $unit, $admin);
        }
    }

    private function seedAuditors(AmiAudit $audit, $auditors, int $index): void
    {
        $lead = $auditors[$index % $auditors->count()];
        $member = $auditors[($index + 1) % $auditors->count()];

        AmiAuditor::updateOrCreate(
            [
                'ami_audit_id' => $audit->id,
                'user_id' => $lead->id,
            ],
            ['role' => AmiAuditorRole::Lead->value],
        );

        AmiAuditor::updateOrCreate(
            [
                'ami_audit_id' => $audit->id,
                'user_id' => $member->id,
            ],
            ['role' => AmiAuditorRole::Member->value],
        );
    }

    private function seedChecklistsAndFindings(AmiAudit $audit, Unit $unit, User $admin): void
    {
        $assignments = IndicatorUnitAssignment::query()
            ->with('standardIndicator')
            ->where('unit_id', $unit->id)
            ->orderBy('standard_indicator_id')
            ->limit(12)
            ->get();

        foreach ($assignments as $index => $assignment) {
            $assessment = $this->assessmentResult($index);

            $checklist = AmiChecklist::updateOrCreate(
                [
                    'ami_audit_id' => $audit->id,
                    'standard_indicator_id' => $assignment->standard_indicator_id,
                ],
                [
                    'assessment_result' => $assessment->value,
                    'auditor_notes' => $this->auditorNotes($assessment, $assignment->standardIndicator?->code ?? 'indikator'),
                ],
            );

            if ($assessment === AmiAssessmentResult::Conform || $assessment === AmiAssessmentResult::NotApplicable) {
                continue;
            }

            $finding = AmiFinding::updateOrCreate(
                [
                    'ami_audit_id' => $audit->id,
                    'ami_checklist_id' => $checklist->id,
                    'standard_indicator_id' => $assignment->standard_indicator_id,
                ],
                [
                    'category' => $this->findingCategory($assessment)->value,
                    'description' => 'Temuan demo '.$assessment->getLabel().' pada '.$unit->name.' untuk indikator '.$assignment->standardIndicator?->code.'.',
                    'root_cause' => 'Dokumentasi pelaksanaan belum sepenuhnya konsisten antar periode.',
                    'recommendation' => 'Unit perlu melengkapi bukti, menetapkan PIC, dan menyusun jadwal pemantauan perbaikan.',
                    'due_date' => now()->addDays(30 + $index)->toDateString(),
                    'status' => AmiFindingStatus::Open->value,
                    'created_by' => $admin->id,
                ],
            );

            $this->seedCorrectiveAction($finding, $unit, $admin, $index);
        }
    }

    private function seedCorrectiveAction(AmiFinding $finding, Unit $unit, User $admin, int $index): void
    {
        $unitPic = User::role('unit_pic')->where('unit_id', $unit->id)->first();
        $status = match ($index % 4) {
            0 => CorrectiveActionStatus::Draft,
            1 => CorrectiveActionStatus::Submitted,
            2 => CorrectiveActionStatus::NeedRevision,
            default => CorrectiveActionStatus::Accepted,
        };

        $correctiveAction = CorrectiveAction::updateOrCreate(
            ['ami_finding_id' => $finding->id],
            [
                'root_cause_analysis' => 'Akar masalah utama adalah belum ada jadwal kontrol dokumen dan bukti mutu yang konsisten.',
                'action_plan' => 'Menyusun daftar bukti, menetapkan PIC, memperbarui dokumen, dan melakukan monitoring mingguan sampai temuan ditutup.',
                'pic_user_id' => $unitPic?->id,
                'target_date' => now()->addDays(20 + $index)->toDateString(),
                'status' => $status->value,
                'submitted_at' => $status === CorrectiveActionStatus::Draft
                    ? null
                    : ($status === CorrectiveActionStatus::Submitted ? now()->subDay() : now()->subDays(3)),
                'submitted_by' => $status === CorrectiveActionStatus::Draft ? null : $unitPic?->id,
            ],
        );

        if ($status === CorrectiveActionStatus::Draft) {
            return;
        }

        CorrectiveActionEvidence::updateOrCreate(
            [
                'corrective_action_id' => $correctiveAction->id,
                'description' => 'Bukti tindak lanjut awal',
            ],
            [
                'file_name' => null,
                'file_path' => null,
                'external_url' => sprintf('https://example.test/tindak-lanjut/%s', $finding->id),
                'uploaded_by' => $unitPic?->id,
            ],
        );

        if ($status === CorrectiveActionStatus::Submitted) {
            $finding->update(['status' => AmiFindingStatus::WaitingVerification->value]);

            return;
        }

        $reviewStatus = $status === CorrectiveActionStatus::Accepted
            ? CorrectiveActionReviewStatus::Accepted
            : CorrectiveActionReviewStatus::NeedRevision;

        CorrectiveActionReview::updateOrCreate(
            [
                'corrective_action_id' => $correctiveAction->id,
                'status' => $reviewStatus->value,
            ],
            [
                'reviewer_id' => $admin->id,
                'notes' => $reviewStatus === CorrectiveActionReviewStatus::Accepted
                    ? 'Tindak lanjut diterima dan temuan ditutup.'
                    : 'Mohon perbaiki jadwal dan lengkapi bukti implementasi.',
                'reviewed_at' => now()->subDay(),
            ],
        );

        $finding->update([
            'status' => $reviewStatus === CorrectiveActionReviewStatus::Accepted
                ? AmiFindingStatus::Closed->value
                : AmiFindingStatus::NeedRevision->value,
        ]);
    }

    private function assessmentResult(int $index): AmiAssessmentResult
    {
        return match ($index % 6) {
            0, 1 => AmiAssessmentResult::Conform,
            2 => AmiAssessmentResult::Observation,
            3 => AmiAssessmentResult::Minor,
            4 => AmiAssessmentResult::Major,
            default => AmiAssessmentResult::Ofi,
        };
    }

    private function findingCategory(AmiAssessmentResult $assessment): AmiFindingCategory
    {
        return match ($assessment) {
            AmiAssessmentResult::Observation => AmiFindingCategory::Observation,
            AmiAssessmentResult::Minor => AmiFindingCategory::Minor,
            AmiAssessmentResult::Major => AmiFindingCategory::Major,
            default => AmiFindingCategory::Ofi,
        };
    }

    private function auditorNotes(AmiAssessmentResult $assessment, string $indicatorCode): string
    {
        return $assessment === AmiAssessmentResult::Conform
            ? 'Indikator '.$indicatorCode.' sudah sesuai dengan bukti yang tersedia.'
            : 'Indikator '.$indicatorCode.' memerlukan perhatian: '.$assessment->getLabel().'.';
    }
}
