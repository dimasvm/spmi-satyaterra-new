<?php

namespace App\Filament\Pages;

use App\Enums\AmiAuditStatus;
use App\Enums\AmiFindingStatus;
use App\Enums\CorrectiveActionStatus;
use App\Enums\ManagementReviewStatus;
use App\Enums\QualityStandardStatus;
use App\Enums\StandardImprovementProposalStatus;
use App\Enums\SubmissionStatus;
use App\Filament\Resources\AchievementReviews\AchievementReviewResource;
use App\Filament\Resources\AmiAudits\AmiAuditResource;
use App\Filament\Resources\IndicatorAchievements\IndicatorAchievementResource;
use App\Filament\Resources\QualityStandards\QualityStandardResource;
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
use App\Models\StandardIndicator;
use App\Models\User;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class SiklusSpmi extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMap;

    protected static string|UnitEnum|null $navigationGroup = 'Siklus SPMI';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Peta Siklus SPMI';

    protected static ?string $title = 'Siklus SPMI';

    protected string $view = 'filament.pages.siklus-spmi';

    public ?int $selectedSpmiPeriodId = null;

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    public function mount(): void
    {
        $this->selectedSpmiPeriodId = SpmiPeriod::active()->value('id')
            ?? SpmiPeriod::query()->latest('start_date')->value('id');
    }

    /**
     * @return array<int, string>
     */
    public function periodOptions(): array
    {
        return SpmiPeriod::query()
            ->orderByDesc('start_date')
            ->pluck('name', 'id')
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function stages(): array
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return [];
        }

        return [
            $this->standardStage(),
            $this->implementationStage($user),
            $this->amiStage($user),
            $this->controlStage($user),
            $this->improvementStage($user),
        ];
    }

    /**
     * @return array<string, string|null>
     */
    public function selectedPeriod(): array
    {
        $period = $this->selectedSpmiPeriodId !== null
            ? SpmiPeriod::query()->find($this->selectedSpmiPeriodId)
            : null;

        return [
            'name' => $period?->name ?? 'Belum ada periode dipilih',
            'status' => $period?->status?->getLabel() ?? 'Tidak aktif',
            'range' => $period?->start_date?->format('d M Y').' - '.$period?->end_date?->format('d M Y'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function standardStage(): array
    {
        $totalStandards = $this->qualityStandardQuery()->count();
        $activeStandards = (clone $this->qualityStandardQuery())
            ->whereIn('status', [
                QualityStandardStatus::Approved->value,
                QualityStandardStatus::Active->value,
            ])
            ->count();
        $draftStandards = (clone $this->qualityStandardQuery())
            ->where('status', QualityStandardStatus::Draft->value)
            ->count();
        $submittedStandards = (clone $this->qualityStandardQuery())
            ->where('status', QualityStandardStatus::Submitted->value)
            ->count();
        $indicatorTotal = $this->standardIndicatorQuery()->count();

        return $this->stage(
            step: 1,
            title: 'Penetapan Standar',
            description: 'Tetapkan standar, indikator, dan target mutu sebagai dasar pelaksanaan.',
            progress: $this->percentage($activeStandards, $totalStandards),
            status: $this->statusLabel($activeStandards, $totalStandards),
            actionLabel: 'Kelola Standar',
            actionUrl: QualityStandardResource::getUrl('index'),
            metrics: [
                $this->metric('Standar aktif', $activeStandards),
                $this->metric('Total indikator', $indicatorTotal),
                $this->metric('Standar draf', $draftStandards),
                $this->metric('Belum disetujui', $draftStandards + $submittedStandards),
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function implementationStage(User $user): array
    {
        $assignments = $this->assignmentQuery($user);
        $assignmentTotal = (clone $assignments)->count();
        $submittedAchievements = (clone $this->achievementQuery($user))
            ->whereIn('submission_status', [
                SubmissionStatus::Submitted->value,
                SubmissionStatus::Validated->value,
            ])
            ->count();
        $pendingReview = (clone $this->achievementQuery($user))
            ->where('submission_status', SubmissionStatus::Submitted->value)
            ->count();
        $notSubmitted = (clone $assignments)->doesntHave('achievements')->count();

        return $this->stage(
            step: 2,
            title: 'Pelaksanaan Standar',
            description: 'Pantau pengisian capaian indikator oleh unit dan proses validasi LPM.',
            progress: $this->percentage($submittedAchievements, $assignmentTotal),
            status: $this->statusLabel($submittedAchievements, $assignmentTotal),
            actionLabel: $user->hasAnyRole(['super_admin', 'admin_lpm']) ? 'Monitoring Capaian' : 'Isi Capaian',
            actionUrl: $user->hasAnyRole(['super_admin', 'admin_lpm'])
                ? AchievementReviewResource::getUrl('index')
                : IndicatorAchievementResource::getUrl('index'),
            metrics: [
                $this->metric('Total assignment', $assignmentTotal),
                $this->metric('Sudah submit', $submittedAchievements),
                $this->metric('Belum submit', $notSubmitted),
                $this->metric('Menunggu validasi', $pendingReview),
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function amiStage(User $user): array
    {
        $auditTotal = (clone $this->auditQuery($user))->count();
        $completedAudits = (clone $this->auditQuery($user))
            ->whereIn('status', [
                AmiAuditStatus::Completed->value,
                AmiAuditStatus::Finalized->value,
            ])
            ->count();
        $checklistTotal = $this->checklistQuery($user)->count();
        $checklistCompleted = (clone $this->checklistQuery($user))
            ->whereNotNull('assessment_result')
            ->count();

        return $this->stage(
            step: 3,
            title: 'Evaluasi AMI',
            description: 'Lihat kesiapan audit, penyelesaian checklist, dan temuan hasil evaluasi.',
            progress: $this->percentage($completedAudits, $auditTotal),
            status: $this->statusLabel($completedAudits, $auditTotal),
            actionLabel: 'AMI Workspace',
            actionUrl: AmiAuditResource::getUrl('index'),
            metrics: [
                $this->metric('Periode AMI aktif', (clone $this->auditQuery($user))->whereIn('status', [
                    AmiAuditStatus::Scheduled->value,
                    AmiAuditStatus::Ongoing->value,
                ])->count()),
                $this->metric('Unit diaudit', $auditTotal),
                $this->metric('Checklist selesai', $checklistCompleted.'/'.$checklistTotal),
                $this->metric('Temuan audit', (clone $this->findingQuery($user))->count()),
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function controlStage(User $user): array
    {
        $correctiveActions = $this->correctiveActionQuery($user);
        $actionTotal = (clone $correctiveActions)->count();
        $acceptedActions = (clone $correctiveActions)
            ->where('status', CorrectiveActionStatus::Accepted->value)
            ->count();

        return $this->stage(
            step: 4,
            title: 'Pengendalian Temuan',
            description: 'Kendalikan temuan audit melalui rencana tindak lanjut dan verifikasi.',
            progress: $this->percentage($acceptedActions, $actionTotal),
            status: $this->statusLabel($acceptedActions, $actionTotal),
            actionLabel: 'Monitoring Tindak Lanjut',
            actionUrl: $this->controlStageUrl($user),
            metrics: [
                $this->metric('Temuan terbuka', (clone $this->findingQuery($user))->whereNot('status', AmiFindingStatus::Closed->value)->count()),
                $this->metric('Tindak lanjut proses', (clone $this->correctiveActionQuery($user))->whereIn('status', [
                    CorrectiveActionStatus::Draft->value,
                    CorrectiveActionStatus::NeedRevision->value,
                ])->count()),
                $this->metric('Menunggu verifikasi', (clone $this->correctiveActionQuery($user))->whereIn('status', [
                    CorrectiveActionStatus::Submitted->value,
                    CorrectiveActionStatus::InReview->value,
                ])->count()),
                $this->metric('Terlambat', $this->overdueCorrectiveActionCount($user)),
            ],
        );
    }

    private function controlStageUrl(User $user): string
    {
        if ($user->hasRole('unit_pic')) {
            return TemuanSaya::getUrl();
        }

        if ($user->hasRole('auditor')) {
            return VerifikasiTindakLanjut::getUrl();
        }

        return MonitoringTemuan::getUrl();
    }

    /**
     * @return array<string, mixed>
     */
    private function improvementStage(User $user): array
    {
        $proposalTotal = (clone $this->proposalQuery($user))->count();
        $implementedProposals = (clone $this->proposalQuery($user))
            ->where('status', StandardImprovementProposalStatus::Implemented->value)
            ->count();

        return $this->stage(
            step: 5,
            title: 'Peningkatan Standar',
            description: 'Tindak lanjuti RTM menjadi usulan revisi standar, indikator, atau target.',
            progress: $this->percentage($implementedProposals, $proposalTotal),
            status: $this->statusLabel($implementedProposals, $proposalTotal),
            actionLabel: 'Peningkatan Standar',
            actionUrl: StandardImprovementProposals::getUrl(),
            metrics: [
                $this->metric('RTM draf', (clone $this->managementReviewQuery($user))->where('status', ManagementReviewStatus::Draft->value)->count()),
                $this->metric('RTM selesai', (clone $this->managementReviewQuery($user))->whereIn('status', [
                    ManagementReviewStatus::Completed->value,
                    ManagementReviewStatus::Closed->value,
                ])->count()),
                $this->metric('Usulan diajukan', (clone $this->proposalQuery($user))->where('status', StandardImprovementProposalStatus::Submitted->value)->count()),
                $this->metric('Usulan approved', (clone $this->proposalQuery($user))->where('status', StandardImprovementProposalStatus::Approved->value)->count()),
            ],
        );
    }

    private function qualityStandardQuery(): Builder
    {
        return QualityStandard::query()
            ->when($this->selectedSpmiPeriodId, fn (Builder $query): Builder => $query->where('spmi_period_id', $this->selectedSpmiPeriodId));
    }

    private function standardIndicatorQuery(): Builder
    {
        return StandardIndicator::query()
            ->when($this->selectedSpmiPeriodId, fn (Builder $query): Builder => $query->whereHas(
                'qualityStandard',
                fn (Builder $standardQuery): Builder => $standardQuery->where('spmi_period_id', $this->selectedSpmiPeriodId),
            ));
    }

    private function assignmentQuery(User $user): Builder
    {
        return IndicatorUnitAssignment::query()
            ->forUser($user)
            ->when($this->selectedSpmiPeriodId, fn (Builder $query): Builder => $query->where('spmi_period_id', $this->selectedSpmiPeriodId));
    }

    private function achievementQuery(User $user): Builder
    {
        return IndicatorAchievement::query()
            ->forUser($user)
            ->when($this->selectedSpmiPeriodId, fn (Builder $query): Builder => $query->whereHas(
                'assignment',
                fn (Builder $assignmentQuery): Builder => $assignmentQuery->where('spmi_period_id', $this->selectedSpmiPeriodId),
            ));
    }

    private function auditQuery(User $user): Builder
    {
        return AmiAudit::query()
            ->forUser($user)
            ->when($this->selectedSpmiPeriodId, fn (Builder $query): Builder => $query->whereHas(
                'amiPeriod',
                fn (Builder $periodQuery): Builder => $periodQuery->where('spmi_period_id', $this->selectedSpmiPeriodId),
            ));
    }

    private function checklistQuery(User $user): Builder
    {
        return AmiChecklist::query()
            ->forUser($user)
            ->when($this->selectedSpmiPeriodId, fn (Builder $query): Builder => $query->whereHas(
                'audit.amiPeriod',
                fn (Builder $periodQuery): Builder => $periodQuery->where('spmi_period_id', $this->selectedSpmiPeriodId),
            ));
    }

    private function findingQuery(User $user): Builder
    {
        return AmiFinding::query()
            ->visibleToUser($user)
            ->when($this->selectedSpmiPeriodId, fn (Builder $query): Builder => $query->whereHas(
                'audit.amiPeriod',
                fn (Builder $periodQuery): Builder => $periodQuery->where('spmi_period_id', $this->selectedSpmiPeriodId),
            ));
    }

    private function correctiveActionQuery(User $user): Builder
    {
        return CorrectiveAction::query()
            ->visibleToUser($user)
            ->when($this->selectedSpmiPeriodId, fn (Builder $query): Builder => $query->whereHas(
                'finding.audit.amiPeriod',
                fn (Builder $periodQuery): Builder => $periodQuery->where('spmi_period_id', $this->selectedSpmiPeriodId),
            ));
    }

    private function managementReviewQuery(User $user): Builder
    {
        return ManagementReview::query()
            ->forUser($user)
            ->when($this->selectedSpmiPeriodId, fn (Builder $query): Builder => $query->where('spmi_period_id', $this->selectedSpmiPeriodId));
    }

    private function proposalQuery(User $user): Builder
    {
        return StandardImprovementProposal::query()
            ->forUser($user)
            ->when($this->selectedSpmiPeriodId, fn (Builder $query): Builder => $query->where(function (Builder $periodQuery): void {
                $periodQuery
                    ->where('target_spmi_period_id', $this->selectedSpmiPeriodId)
                    ->orWhereHas('managementReview', fn (Builder $reviewQuery): Builder => $reviewQuery->where('spmi_period_id', $this->selectedSpmiPeriodId));
            }));
    }

    private function overdueCorrectiveActionCount(User $user): int
    {
        return (clone $this->correctiveActionQuery($user))
            ->whereNot('status', CorrectiveActionStatus::Accepted->value)
            ->where(function (Builder $query): void {
                $query
                    ->whereDate('target_date', '<', today())
                    ->orWhereHas('finding', fn (Builder $findingQuery): Builder => $findingQuery->whereDate('due_date', '<', today()));
            })
            ->count();
    }

    /**
     * @param  array<int, array<string, int|string>>  $metrics
     * @return array<string, mixed>
     */
    private function stage(int $step, string $title, string $description, int $progress, string $status, string $actionLabel, string $actionUrl, array $metrics): array
    {
        return compact('step', 'title', 'description', 'progress', 'status', 'actionLabel', 'actionUrl', 'metrics');
    }

    /**
     * @return array<string, int|string>
     */
    private function metric(string $label, int|string $value): array
    {
        return compact('label', 'value');
    }

    private function percentage(int $value, int $total): int
    {
        if ($total < 1) {
            return 0;
        }

        return (int) round(($value / $total) * 100);
    }

    private function statusLabel(int $value, int $total): string
    {
        $percentage = $this->percentage($value, $total);

        return match (true) {
            $total < 1 => 'Belum ada data',
            $percentage >= 80 => 'Terkendali',
            $percentage >= 40 => 'Berjalan',
            default => 'Perlu perhatian',
        };
    }
}
