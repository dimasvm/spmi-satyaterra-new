<?php

namespace App\Filament\Widgets;

use App\Enums\AchievementStatus;
use App\Enums\AmiAuditStatus;
use App\Enums\AmiFindingCategory;
use App\Enums\AmiFindingStatus;
use App\Enums\CorrectiveActionStatus;
use App\Enums\ManagementReviewStatus;
use App\Enums\QualityStandardStatus;
use App\Enums\StandardImprovementProposalStatus;
use App\Enums\SubmissionStatus;
use App\Filament\Pages\AssignIndikator;
use App\Filament\Pages\AuditSaya;
use App\Filament\Pages\CapaianIndikatorSaya;
use App\Filament\Pages\InboxValidasiCapaian;
use App\Filament\Pages\ManagementReviews;
use App\Filament\Pages\MonitoringTemuan;
use App\Filament\Pages\ReportsPage;
use App\Filament\Pages\StandardImprovementProposals;
use App\Filament\Pages\TemuanSaya;
use App\Filament\Pages\VerifikasiTindakLanjut;
use App\Filament\Resources\AmiAudits\AmiAuditResource;
use App\Filament\Resources\IndicatorAchievements\IndicatorAchievementResource;
use App\Filament\Resources\IndicatorUnitAssignments\IndicatorUnitAssignmentResource;
use App\Filament\Resources\QualityStandards\QualityStandardResource;
use App\Filament\Widgets\Concerns\InteractsWithSpmiDashboard;
use App\Models\AmiAudit;
use App\Models\AmiChecklist;
use App\Models\AmiFinding;
use App\Models\CorrectiveAction;
use App\Models\IndicatorAchievement;
use App\Models\IndicatorUnitAssignment;
use App\Models\ManagementReview;
use App\Models\QualityStandard;
use App\Models\SpmiPeriod;
use App\Models\StandardImprovementProposal;
use App\Models\Unit;
use App\Models\User;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;

class SpmiCommandCenter extends Widget
{
    use InteractsWithSpmiDashboard;

    protected string $view = 'filament.widgets.spmi-command-center';

    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->check();
    }

    /**
     * @return array<string, mixed>
     */
    public function dashboard(): array
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return $this->emptyDashboard();
        }

        if ($user->hasAnyRole(['super_admin', 'admin_lpm'])) {
            return $this->managementDashboard($user);
        }

        if ($user->hasAnyRole(['pimpinan', 'viewer'])) {
            return $this->leaderDashboard($user);
        }

        if ($user->hasRole('unit_pic')) {
            return $this->unitDashboard($user);
        }

        if ($user->hasRole('auditor')) {
            return $this->auditorDashboard($user);
        }

        return $this->emptyDashboard();
    }

    /**
     * @return array<string, mixed>
     */
    private function managementDashboard(User $user): array
    {
        $assignmentTotal = (clone $this->assignmentBaseQuery($user))->count();
        $submittedAchievements = (clone $this->achievementBaseQuery($user))
            ->where('submission_status', SubmissionStatus::Submitted->value)
            ->count();
        $validatedAchievements = (clone $this->achievementBaseQuery($user))
            ->where('submission_status', SubmissionStatus::Validated->value)
            ->count();
        $activeFindings = (clone $this->findingBaseQuery($user))
            ->whereNot('status', AmiFindingStatus::Closed->value)
            ->count();
        $acceptedActions = (clone $this->correctiveActionBaseQuery($user))
            ->where('status', CorrectiveActionStatus::Accepted->value)
            ->count();
        $correctiveActionsTotal = (clone $this->correctiveActionBaseQuery($user))->count();
        $implementedProposals = (clone $this->proposalBaseQuery($user))
            ->where('status', StandardImprovementProposalStatus::Implemented->value)
            ->count();
        $proposalTotal = (clone $this->proposalBaseQuery($user))->count();

        return [
            'role_label' => 'Command Center Admin LPM',
            'title' => 'Prioritas Kerja SPMI',
            'description' => 'Pantau antrean kerja, risiko keterlambatan, dan progres PPEPP periode aktif.',
            'period' => $this->periodSummary(),
            'shortcuts' => $this->managementShortcuts(),
            'stats' => [
                $this->stat('Capaian Menunggu Validasi', $submittedAchievements, 'Perlu diputuskan LPM', 'warning', InboxValidasiCapaian::getUrl()),
                $this->stat('Unit Belum Submit', (clone $this->assignmentBaseQuery($user))->doesntHave('achievements')->count(), 'Belum ada capaian', 'danger', IndicatorUnitAssignmentResource::getUrl('index')),
                $this->stat('Temuan Aktif', $activeFindings, 'Belum ditutup', 'warning', MonitoringTemuan::getUrl()),
                $this->stat('Usulan Diproses', (clone $this->proposalBaseQuery($user))->whereIn('status', [
                    StandardImprovementProposalStatus::Draft->value,
                    StandardImprovementProposalStatus::Submitted->value,
                    StandardImprovementProposalStatus::Approved->value,
                ])->count(), 'Peningkatan standar', 'info', StandardImprovementProposals::getUrl()),
            ],
            'ppepp' => [
                $this->progressCard('Penetapan Standar', $this->activeStandardsCount(), $this->standardsCount(), 'Standar aktif', QualityStandardResource::getUrl('index')),
                $this->progressCard('Pelaksanaan Standar', $validatedAchievements, max($assignmentTotal, 1), 'Capaian tervalidasi', IndicatorAchievementResource::getUrl('index')),
                $this->progressCard('Evaluasi AMI', (clone $this->auditBaseQuery($user))->whereIn('status', [
                    AmiAuditStatus::Completed->value,
                    AmiAuditStatus::Finalized->value,
                ])->count(), max((clone $this->auditBaseQuery($user))->count(), 1), 'Audit selesai', AmiAuditResource::getUrl('index')),
                $this->progressCard('Pengendalian Temuan', $acceptedActions, max($correctiveActionsTotal, 1), 'Tindak lanjut diterima', MonitoringTemuan::getUrl()),
                $this->progressCard('Peningkatan Standar', $implementedProposals, max($proposalTotal, 1), 'Usulan diimplementasikan', StandardImprovementProposals::getUrl()),
            ],
            'queues' => [
                $this->queueItem('Capaian menunggu validasi', $submittedAchievements, 'warning', InboxValidasiCapaian::getUrl()),
                $this->queueItem('Unit belum submit capaian', (clone $this->assignmentBaseQuery($user))->doesntHave('achievements')->count(), 'danger', IndicatorUnitAssignmentResource::getUrl('index')),
                $this->queueItem('Capaian dikembalikan', (clone $this->achievementBaseQuery($user))->where('submission_status', SubmissionStatus::Returned->value)->count(), 'warning', IndicatorAchievementResource::getUrl('index')),
                $this->queueItem('Temuan belum ditindaklanjuti', (clone $this->findingBaseQuery($user))->doesntHave('correctiveActions')->whereNot('status', AmiFindingStatus::Closed->value)->count(), 'danger', MonitoringTemuan::getUrl()),
                $this->queueItem('Tindak lanjut menunggu verifikasi', (clone $this->correctiveActionBaseQuery($user))->whereIn('status', [
                    CorrectiveActionStatus::Submitted->value,
                    CorrectiveActionStatus::InReview->value,
                ])->count(), 'info', VerifikasiTindakLanjut::getUrl()),
                $this->queueItem('Usulan peningkatan menunggu proses', (clone $this->proposalBaseQuery($user))->where('status', StandardImprovementProposalStatus::Submitted->value)->count(), 'info', StandardImprovementProposals::getUrl()),
            ],
            'warnings' => $this->managementWarnings($user),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function unitDashboard(User $user): array
    {
        return [
            'role_label' => 'Command Center Unit/PIC',
            'title' => 'Tugas Mutu Unit',
            'description' => 'Fokus pada indikator yang harus diisi, capaian yang dikembalikan, dan tindak lanjut temuan unit.',
            'period' => $this->periodSummary(),
            'shortcuts' => [
                $this->shortcut('Isi Capaian', CapaianIndikatorSaya::getUrl()),
                $this->shortcut('Temuan Saya', TemuanSaya::getUrl()),
            ],
            'stats' => [
                $this->stat('Indikator Ditugaskan', (clone $this->assignmentBaseQuery($user))->count(), 'Total tugas periode ini', 'info', IndicatorUnitAssignmentResource::getUrl('index')),
                $this->stat('Belum Diisi', (clone $this->assignmentBaseQuery($user))->doesntHave('achievements')->count(), 'Perlu mulai capaian', 'danger', CapaianIndikatorSaya::getUrl()),
                $this->stat('Draft', (clone $this->achievementBaseQuery($user))->where('submission_status', SubmissionStatus::Draft->value)->count(), 'Belum dikirim', 'gray', CapaianIndikatorSaya::getUrl()),
                $this->stat('Menunggu Validasi', (clone $this->achievementBaseQuery($user))->where('submission_status', SubmissionStatus::Submitted->value)->count(), 'Sedang direview LPM', 'info', CapaianIndikatorSaya::getUrl()),
                $this->stat('Dikembalikan', (clone $this->achievementBaseQuery($user))->where('submission_status', SubmissionStatus::Returned->value)->count(), 'Perlu perbaikan', 'warning', CapaianIndikatorSaya::getUrl()),
                $this->stat('Tervalidasi', (clone $this->achievementBaseQuery($user))->where('submission_status', SubmissionStatus::Validated->value)->count(), 'Selesai', 'success', CapaianIndikatorSaya::getUrl()),
                $this->stat('Temuan Aktif', (clone $this->findingBaseQuery($user))->whereNot('status', AmiFindingStatus::Closed->value)->count(), 'Belum ditutup', 'warning', TemuanSaya::getUrl()),
                $this->stat('Tindak Lanjut Revisi', (clone $this->correctiveActionBaseQuery($user))->where('status', CorrectiveActionStatus::NeedRevision->value)->count(), 'Perlu diperbaiki', 'danger', TemuanSaya::getUrl()),
            ],
            'queues' => [
                $this->queueItem('Indikator yang harus diisi', (clone $this->assignmentBaseQuery($user))->doesntHave('achievements')->count(), 'danger', CapaianIndikatorSaya::getUrl()),
                $this->queueItem('Capaian yang dikembalikan', (clone $this->achievementBaseQuery($user))->where('submission_status', SubmissionStatus::Returned->value)->count(), 'warning', CapaianIndikatorSaya::getUrl()),
                $this->queueItem('Temuan audit yang harus ditindaklanjuti', (clone $this->findingBaseQuery($user))->doesntHave('correctiveActions')->whereNot('status', AmiFindingStatus::Closed->value)->count(), 'danger', TemuanSaya::getUrl()),
                $this->queueItem('Deadline terdekat', (clone $this->assignmentBaseQuery($user))->whereDate('due_date', '>=', today())->whereDate('due_date', '<=', today()->addDays(14))->count(), 'info', IndicatorUnitAssignmentResource::getUrl('index')),
            ],
            'warnings' => $this->unitWarnings($user),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function auditorDashboard(User $user): array
    {
        return [
            'role_label' => 'Command Center Auditor',
            'title' => 'Tugas Audit Saya',
            'description' => 'Ringkasan audit yang ditugaskan, checklist yang belum selesai, dan verifikasi tindak lanjut.',
            'period' => $this->periodSummary(),
            'shortcuts' => [
                $this->shortcut('Audit Saya', AuditSaya::getUrl()),
                $this->shortcut('Verifikasi Tindak Lanjut', VerifikasiTindakLanjut::getUrl()),
            ],
            'stats' => [
                $this->stat('Audit Ditugaskan', (clone $this->auditBaseQuery($user))->count(), 'Audit untuk saya', 'info', AuditSaya::getUrl()),
                $this->stat('Checklist Belum Selesai', $this->unfinishedChecklistCount($user), 'Perlu dilengkapi', 'warning', AuditSaya::getUrl()),
                $this->stat('Temuan Belum Final', (clone $this->findingBaseQuery($user))->whereNot('status', AmiFindingStatus::Closed->value)->count(), 'Masih terbuka', 'warning', AuditSaya::getUrl()),
                $this->stat('Menunggu Verifikasi', (clone $this->correctiveActionBaseQuery($user))->whereIn('status', [
                    CorrectiveActionStatus::Submitted->value,
                    CorrectiveActionStatus::InReview->value,
                ])->count(), 'Tindak lanjut unit', 'info', VerifikasiTindakLanjut::getUrl()),
            ],
            'queues' => [
                $this->queueItem('Audit yang ditugaskan kepada saya', (clone $this->auditBaseQuery($user))->count(), 'info', AuditSaya::getUrl()),
                $this->queueItem('Checklist belum selesai', $this->unfinishedChecklistCount($user), 'warning', AuditSaya::getUrl()),
                $this->queueItem('Temuan belum difinalisasi', (clone $this->findingBaseQuery($user))->whereNot('status', AmiFindingStatus::Closed->value)->count(), 'warning', AuditSaya::getUrl()),
                $this->queueItem('Tindak lanjut menunggu verifikasi', (clone $this->correctiveActionBaseQuery($user))->whereIn('status', [
                    CorrectiveActionStatus::Submitted->value,
                    CorrectiveActionStatus::InReview->value,
                ])->count(), 'info', VerifikasiTindakLanjut::getUrl()),
            ],
            'warnings' => [
                $this->queueItem('Audit belum final', (clone $this->auditBaseQuery($user))->whereNot('status', AmiAuditStatus::Finalized->value)->count(), 'warning', AuditSaya::getUrl()),
                $this->queueItem('Tindak lanjut terlambat', $this->overdueCorrectiveActionsCount($user), 'danger', VerifikasiTindakLanjut::getUrl()),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function leaderDashboard(User $user): array
    {
        $measuredAchievements = (clone $this->achievementBaseQuery($user))
            ->whereNotNull('achievement_status')
            ->count();
        $achieved = (clone $this->achievementBaseQuery($user))
            ->where('achievement_status', AchievementStatus::Achieved->value)
            ->count();

        return [
            'role_label' => 'Command Center Pimpinan',
            'title' => 'Ringkasan Keputusan Mutu',
            'description' => 'Pantau capaian institusi, temuan penting, dan usulan peningkatan yang membutuhkan keputusan.',
            'period' => $this->periodSummary(),
            'shortcuts' => [
                $this->shortcut('Laporan', ReportsPage::getUrl()),
                $this->shortcut('Usulan Peningkatan', StandardImprovementProposals::getUrl()),
                $this->shortcut('RTM', ManagementReviews::getUrl()),
            ],
            'stats' => [
                $this->stat('Capaian Standar Institusi', $this->percentage($achieved, $measuredAchievements).'%', 'Capaian terukur', 'success', ReportsPage::getUrl()),
                $this->stat('Temuan Mayor', (clone $this->findingBaseQuery($user))->where('category', AmiFindingCategory::Major->value)->count(), 'Prioritas pimpinan', 'danger', MonitoringTemuan::getUrl()),
                $this->stat('Temuan Minor', (clone $this->findingBaseQuery($user))->where('category', AmiFindingCategory::Minor->value)->count(), 'Perlu pengendalian', 'warning', MonitoringTemuan::getUrl()),
                $this->stat('Approval Usulan', (clone $this->proposalBaseQuery($user))->where('status', StandardImprovementProposalStatus::Submitted->value)->count(), 'Menunggu keputusan', 'info', StandardImprovementProposals::getUrl()),
            ],
            'unit_progress' => $this->unitProgressHighlights(),
            'queues' => [
                $this->queueItem('Tindak lanjut strategis belum selesai', (clone $this->correctiveActionBaseQuery($user))->whereNot('status', CorrectiveActionStatus::Accepted->value)->count(), 'warning', MonitoringTemuan::getUrl()),
                $this->queueItem('Usulan peningkatan menunggu approval', (clone $this->proposalBaseQuery($user))->where('status', StandardImprovementProposalStatus::Submitted->value)->count(), 'info', StandardImprovementProposals::getUrl()),
                $this->queueItem('RTM belum difinalisasi', (clone $this->managementReviewBaseQuery($user))->whereIn('status', [
                    ManagementReviewStatus::Draft->value,
                    ManagementReviewStatus::Scheduled->value,
                    ManagementReviewStatus::Completed->value,
                ])->count(), 'warning', ManagementReviews::getUrl()),
            ],
            'warnings' => $this->managementWarnings($user),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyDashboard(): array
    {
        return [
            'role_label' => 'Dashboard SPMI',
            'title' => 'Belum ada dashboard untuk role ini',
            'description' => 'Hubungi administrator jika Anda membutuhkan akses workflow SPMI.',
            'period' => $this->periodSummary(),
            'shortcuts' => [],
            'stats' => [],
            'queues' => [],
            'warnings' => [],
        ];
    }

    private function assignmentBaseQuery(User $user): Builder
    {
        return $this->applyAssignmentDashboardScope(
            IndicatorUnitAssignment::query()->when($user, fn (Builder $query): Builder => $query->forUser($user)),
        );
    }

    private function achievementBaseQuery(User $user): Builder
    {
        return $this->applyAchievementDashboardScope(
            IndicatorAchievement::query()->when($user, fn (Builder $query): Builder => $query->forUser($user)),
        );
    }

    private function auditBaseQuery(User $user): Builder
    {
        $periodId = $this->selectedSpmiPeriodId();

        return AmiAudit::query()
            ->forUser($user)
            ->when($periodId, fn (Builder $query): Builder => $query->whereHas(
                'amiPeriod',
                fn (Builder $periodQuery): Builder => $periodQuery->where('spmi_period_id', $periodId),
            ));
    }

    private function findingBaseQuery(User $user): Builder
    {
        $periodId = $this->selectedSpmiPeriodId();

        return AmiFinding::query()
            ->visibleToUser($user)
            ->when($periodId, fn (Builder $query): Builder => $query->whereHas(
                'audit.amiPeriod',
                fn (Builder $periodQuery): Builder => $periodQuery->where('spmi_period_id', $periodId),
            ));
    }

    private function correctiveActionBaseQuery(User $user): Builder
    {
        $periodId = $this->selectedSpmiPeriodId();

        return CorrectiveAction::query()
            ->visibleToUser($user)
            ->when($periodId, fn (Builder $query): Builder => $query->whereHas(
                'finding.audit.amiPeriod',
                fn (Builder $periodQuery): Builder => $periodQuery->where('spmi_period_id', $periodId),
            ));
    }

    private function proposalBaseQuery(User $user): Builder
    {
        $periodId = $this->selectedSpmiPeriodId();

        return StandardImprovementProposal::query()
            ->forUser($user)
            ->when($periodId, fn (Builder $query): Builder => $query->where(function (Builder $periodQuery) use ($periodId): void {
                $periodQuery
                    ->where('target_spmi_period_id', $periodId)
                    ->orWhereHas('managementReview', fn (Builder $reviewQuery): Builder => $reviewQuery->where('spmi_period_id', $periodId));
            }));
    }

    private function managementReviewBaseQuery(User $user): Builder
    {
        $periodId = $this->selectedSpmiPeriodId();

        return ManagementReview::query()
            ->forUser($user)
            ->when($periodId, fn (Builder $query): Builder => $query->where('spmi_period_id', $periodId));
    }

    /**
     * @return array<string, string|null>
     */
    private function periodSummary(): array
    {
        $period = $this->selectedSpmiPeriodId()
            ? SpmiPeriod::query()->find($this->selectedSpmiPeriodId())
            : null;

        return [
            'name' => $period?->name ?? 'Belum ada periode aktif',
            'status' => $period?->status?->getLabel() ?? 'Tidak aktif',
            'cycle' => $this->cycleStatus(),
        ];
    }

    private function cycleStatus(): string
    {
        $periodId = $this->selectedSpmiPeriodId();

        if ($periodId === null) {
            return 'Periode belum dipilih';
        }

        if (StandardImprovementProposal::query()->where('target_spmi_period_id', $periodId)->exists()) {
            return 'Peningkatan';
        }

        if (ManagementReview::query()->where('spmi_period_id', $periodId)->exists()) {
            return 'Pengendalian';
        }

        if (AmiAudit::query()->whereHas('amiPeriod', fn (Builder $query): Builder => $query->where('spmi_period_id', $periodId))->exists()) {
            return 'Evaluasi AMI';
        }

        if (IndicatorUnitAssignment::query()->where('spmi_period_id', $periodId)->exists()) {
            return 'Pelaksanaan';
        }

        return 'Penetapan';
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function managementShortcuts(): array
    {
        return [
            $this->shortcut('Buat Standar', QualityStandardResource::getUrl('create')),
            $this->shortcut('Assign Indikator', AssignIndikator::getUrl()),
            $this->shortcut('Validasi Capaian', InboxValidasiCapaian::getUrl()),
            $this->shortcut('Jadwalkan AMI', AmiAuditResource::getUrl('create')),
            $this->shortcut('Buat RTM', ManagementReviews::getUrl()),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function managementWarnings(User $user): array
    {
        return [
            $this->queueItem('Deadline capaian lewat', $this->overdueAssignmentsCount($user), 'danger', IndicatorUnitAssignmentResource::getUrl('index')),
            $this->queueItem('Tindak lanjut terlambat', $this->overdueCorrectiveActionsCount($user), 'danger', MonitoringTemuan::getUrl()),
            $this->queueItem('AMI belum punya auditor', (clone $this->auditBaseQuery($user))->doesntHave('auditorAssignments')->count(), 'warning', AmiAuditResource::getUrl('index')),
            $this->queueItem('RTM belum difinalisasi', (clone $this->managementReviewBaseQuery($user))->whereIn('status', [
                ManagementReviewStatus::Draft->value,
                ManagementReviewStatus::Scheduled->value,
                ManagementReviewStatus::Completed->value,
            ])->count(), 'warning', ManagementReviews::getUrl()),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function unitWarnings(User $user): array
    {
        return [
            $this->queueItem('Deadline capaian lewat', $this->overdueAssignmentsCount($user), 'danger', IndicatorUnitAssignmentResource::getUrl('index')),
            $this->queueItem('Tindak lanjut terlambat', $this->overdueCorrectiveActionsCount($user), 'danger', TemuanSaya::getUrl()),
        ];
    }

    private function unfinishedChecklistCount(User $user): int
    {
        $periodId = $this->selectedSpmiPeriodId();

        return AmiChecklist::query()
            ->whereHas('audit', fn (Builder $query): Builder => $query->forUser($user))
            ->when($periodId, fn (Builder $query): Builder => $query->whereHas(
                'audit.amiPeriod',
                fn (Builder $periodQuery): Builder => $periodQuery->where('spmi_period_id', $periodId),
            ))
            ->whereNull('assessment_result')
            ->count();
    }

    private function overdueAssignmentsCount(User $user): int
    {
        return (clone $this->assignmentBaseQuery($user))
            ->whereDate('due_date', '<', today())
            ->whereDoesntHave('achievements', fn (Builder $query): Builder => $query->where('submission_status', SubmissionStatus::Validated->value))
            ->count();
    }

    private function overdueCorrectiveActionsCount(User $user): int
    {
        return (clone $this->correctiveActionBaseQuery($user))
            ->whereNot('status', CorrectiveActionStatus::Accepted->value)
            ->where(function (Builder $query): void {
                $query
                    ->whereDate('target_date', '<', today())
                    ->orWhereHas('finding', fn (Builder $findingQuery): Builder => $findingQuery->whereDate('due_date', '<', today()));
            })
            ->count();
    }

    private function standardsCount(): int
    {
        $periodId = $this->selectedSpmiPeriodId();

        return QualityStandard::query()
            ->when($periodId, fn (Builder $query): Builder => $query->where('spmi_period_id', $periodId))
            ->count();
    }

    private function activeStandardsCount(): int
    {
        $periodId = $this->selectedSpmiPeriodId();

        return QualityStandard::query()
            ->when($periodId, fn (Builder $query): Builder => $query->where('spmi_period_id', $periodId))
            ->whereIn('status', [
                QualityStandardStatus::Approved->value,
                QualityStandardStatus::Active->value,
            ])
            ->count();
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function unitProgressHighlights(): array
    {
        $periodId = $this->selectedSpmiPeriodId();

        $units = Unit::query()
            ->active()
            ->withCount([
                'indicatorAssignments as assignments_count' => fn (Builder $query): Builder => $query
                    ->when($periodId, fn (Builder $periodQuery): Builder => $periodQuery->where('spmi_period_id', $periodId)),
                'indicatorAssignments as validated_assignments_count' => fn (Builder $query): Builder => $query
                    ->when($periodId, fn (Builder $periodQuery): Builder => $periodQuery->where('spmi_period_id', $periodId))
                    ->whereHas('achievements', fn (Builder $achievementQuery): Builder => $achievementQuery->where('submission_status', SubmissionStatus::Validated->value)),
            ])
            ->get()
            ->map(fn (Unit $unit): array => [
                'name' => $unit->name,
                'percentage' => $this->percentage((int) $unit->validated_assignments_count, (int) $unit->assignments_count),
                'value' => (int) $unit->validated_assignments_count,
                'total' => (int) $unit->assignments_count,
            ])
            ->sortByDesc('percentage')
            ->values();

        return [
            'best' => $units->take(3)->all(),
            'lowest' => $units->reverse()->take(3)->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function stat(string $label, int|string $value, string $description, string $color, string $url): array
    {
        return compact('label', 'value', 'description', 'color', 'url');
    }

    /**
     * @return array<string, mixed>
     */
    private function progressCard(string $label, int $value, int $total, string $description, string $url): array
    {
        $percentage = $this->percentage($value, $total);

        return compact('label', 'value', 'total', 'description', 'url', 'percentage') + [
            'status' => $percentage >= 80 ? 'Terkendali' : ($percentage >= 40 ? 'Berjalan' : 'Perlu perhatian'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function queueItem(string $label, int $count, string $color, string $url): array
    {
        return compact('label', 'count', 'color', 'url');
    }

    /**
     * @return array<string, string>
     */
    private function shortcut(string $label, string $url): array
    {
        return compact('label', 'url');
    }

    private function percentage(int $value, int $total): int
    {
        if ($total < 1) {
            return 0;
        }

        return (int) round(($value / $total) * 100);
    }
}
