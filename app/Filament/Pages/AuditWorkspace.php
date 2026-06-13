<?php

namespace App\Filament\Pages;

use App\Enums\AmiAssessmentResult;
use App\Enums\AmiAuditorRole;
use App\Enums\AmiAuditStatus;
use App\Enums\AmiFindingCategory;
use App\Enums\AmiFindingStatus;
use App\Enums\EvidenceFileType;
use App\Models\AchievementEvidence;
use App\Models\AchievementReview;
use App\Models\AmiAudit;
use App\Models\AmiChecklist;
use App\Models\AmiFinding;
use App\Models\IndicatorAchievement;
use App\Models\IndicatorUnitAssignment;
use App\Models\StandardIndicator;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class AuditWorkspace extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'audit-workspace/{audit}';

    protected static ?string $title = 'Ruang Kerja Audit';

    protected string $view = 'filament.pages.audit-workspace';

    public AmiAudit $record;

    public string $activeTab = 'summary';

    public bool $isAssessmentModalOpen = false;

    public ?int $editingChecklistId = null;

    public ?string $assessmentResult = null;

    public ?string $auditorNotes = null;

    public bool $isFindingModalOpen = false;

    public ?int $editingFindingId = null;

    public ?int $findingChecklistId = null;

    public ?string $findingCategory = null;

    public ?string $findingDescription = null;

    public ?string $findingRootCause = null;

    public ?string $findingRecommendation = null;

    public ?string $findingDueDate = null;

    public ?string $findingStatus = null;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return (bool) ($user?->isAuditor() || $user?->isAdminLpm() || $user?->isSuperAdmin());
    }

    public function mount(int|string $audit): void
    {
        $this->record = AmiAudit::query()
            ->with($this->recordRelations())
            ->findOrFail($audit);

        abort_unless($this->canViewAudit(), 403);
    }

    /**
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Kembali')
                ->icon(Heroicon::ArrowLeft)
                ->color('gray')
                ->url(AuditSaya::getUrl()),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function tabs(): array
    {
        return [
            'summary' => 'Ringkasan',
            'checklists' => 'Checklist',
            'evidence' => 'Bukti Capaian',
            'findings' => 'Temuan',
            'finalize' => 'Finalisasi',
        ];
    }

    public function setTab(string $tab): void
    {
        if (array_key_exists($tab, $this->tabs())) {
            $this->activeTab = $tab;
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $checklists = $this->record->checklists;
        $total = $checklists->count();
        $completed = $checklists->whereNotNull('assessment_result')->count();

        return [
            'unit' => $this->record->auditeeUnit?->name ?? '-',
            'period' => $this->record->amiPeriod?->name ?? '-',
            'scheduled_date' => $this->record->scheduled_date?->format('d M Y') ?? '-',
            'status' => $this->record->status,
            'progress' => $total > 0 ? (int) round(($completed / $total) * 100) : 0,
            'completed_checklists' => $completed,
            'total_checklists' => $total,
            'findings' => $this->record->findings->count(),
            'conform' => $checklists->where('assessment_result', AmiAssessmentResult::Conform)->count(),
            'minor' => $checklists->where('assessment_result', AmiAssessmentResult::Minor)->count(),
            'major' => $checklists->where('assessment_result', AmiAssessmentResult::Major)->count(),
            'observation' => $checklists->where('assessment_result', AmiAssessmentResult::Observation)->count(),
            'ofi' => $checklists->where('assessment_result', AmiAssessmentResult::Ofi)->count(),
        ];
    }

    /**
     * @return Collection<int, AmiChecklist>
     */
    public function checklists(): Collection
    {
        return $this->record->checklists
            ->sortBy(fn (AmiChecklist $checklist): string => ($checklist->standardIndicator?->qualityStandard?->name ?? '').($checklist->standardIndicator?->code ?? ''))
            ->values();
    }

    /**
     * @return Collection<int, AmiFinding>
     */
    public function findings(): Collection
    {
        return $this->record->findings
            ->sortByDesc('created_at')
            ->values();
    }

    /**
     * @return array<string, array{standard: string, indicator: string, evidences: Collection<int, AchievementEvidence>}>
     */
    public function evidenceGroups(): array
    {
        $groups = [];

        foreach ($this->checklists() as $checklist) {
            $indicator = $checklist->standardIndicator;
            $achievement = $this->unitAchievement($checklist);

            $groups[(string) $checklist->id] = [
                'standard' => $indicator?->qualityStandard?->name ?? 'Standar belum tersedia',
                'indicator' => trim(($indicator?->code ? "{$indicator->code} - " : '').($indicator?->statement ?? 'Indikator belum tersedia')),
                'evidences' => $achievement?->evidences ?? new Collection,
            ];
        }

        return $groups;
    }

    public function openAssessment(int $checklistId): void
    {
        $checklist = $this->findChecklist($checklistId);

        abort_unless($this->canAssessChecklist($checklist), 403);

        $this->editingChecklistId = $checklist->id;
        $this->assessmentResult = $checklist->assessment_result?->value;
        $this->auditorNotes = $checklist->auditor_notes;
        $this->resetValidation();
        $this->isAssessmentModalOpen = true;
    }

    public function closeAssessment(): void
    {
        $this->isAssessmentModalOpen = false;
        $this->editingChecklistId = null;
        $this->assessmentResult = null;
        $this->auditorNotes = null;
        $this->resetValidation();
    }

    public function saveAssessment(): void
    {
        $this->validate([
            'assessmentResult' => ['required', 'string', 'in:'.implode(',', array_column(AmiAssessmentResult::cases(), 'value'))],
            'auditorNotes' => ['nullable', 'string', 'max:2000'],
        ]);

        $checklist = $this->currentEditableChecklist();

        $checklist->update([
            'assessment_result' => $this->assessmentResult,
            'auditor_notes' => $this->auditorNotes,
        ]);

        Notification::make()
            ->success()
            ->title('Assessment checklist disimpan.')
            ->send();

        $this->closeAssessment();
        $this->refreshRecord();
    }

    public function openFindingFromChecklist(int $checklistId): void
    {
        $checklist = $this->findChecklist($checklistId);

        abort_unless($this->canCreateFindingFromChecklist($checklist), 403);

        $this->editingFindingId = null;
        $this->findingChecklistId = $checklist->id;
        $this->findingCategory = $this->categoryFromAssessment($checklist->assessment_result)?->value;
        $this->findingDescription = $checklist->auditor_notes;
        $this->findingRootCause = null;
        $this->findingRecommendation = null;
        $this->findingDueDate = null;
        $this->findingStatus = AmiFindingStatus::Open->value;
        $this->resetValidation();
        $this->isFindingModalOpen = true;
    }

    public function openFindingEdit(int $findingId): void
    {
        $finding = $this->findFinding($findingId);

        abort_unless($this->canManageFinding($finding), 403);

        $this->editingFindingId = $finding->id;
        $this->findingChecklistId = $finding->ami_checklist_id;
        $this->findingCategory = $finding->category?->value;
        $this->findingDescription = $finding->description;
        $this->findingRootCause = $finding->root_cause;
        $this->findingRecommendation = $finding->recommendation;
        $this->findingDueDate = $finding->due_date?->format('Y-m-d');
        $this->findingStatus = $finding->status?->value;
        $this->resetValidation();
        $this->isFindingModalOpen = true;
    }

    public function closeFinding(): void
    {
        $this->isFindingModalOpen = false;
        $this->editingFindingId = null;
        $this->findingChecklistId = null;
        $this->findingCategory = null;
        $this->findingDescription = null;
        $this->findingRootCause = null;
        $this->findingRecommendation = null;
        $this->findingDueDate = null;
        $this->findingStatus = null;
        $this->resetValidation();
    }

    public function saveFinding(): void
    {
        $this->validate([
            'findingCategory' => ['required', 'string', 'in:'.implode(',', array_column(AmiFindingCategory::cases(), 'value'))],
            'findingDescription' => ['required', 'string', 'max:5000'],
            'findingRootCause' => ['nullable', 'string', 'max:2000'],
            'findingRecommendation' => ['nullable', 'string', 'max:5000'],
            'findingDueDate' => ['nullable', 'date'],
            'findingStatus' => ['required', 'string', 'in:'.implode(',', array_column(AmiFindingStatus::cases(), 'value'))],
        ]);

        abort_unless($this->record->status !== AmiAuditStatus::Finalized, 403);

        DB::transaction(function (): void {
            $checklist = $this->findingChecklistId !== null
                ? $this->findChecklist($this->findingChecklistId)
                : null;

            if ($this->editingFindingId !== null) {
                $finding = $this->findFinding($this->editingFindingId);
                abort_unless($this->canManageFinding($finding), 403);

                $finding->update($this->findingPayload($checklist));

                return;
            }

            abort_unless($checklist instanceof AmiChecklist && $this->canCreateFindingFromChecklist($checklist), 403);

            AmiFinding::query()->create($this->findingPayload($checklist));
        });

        Notification::make()
            ->success()
            ->title('Temuan audit disimpan.')
            ->send();

        $this->closeFinding();
        $this->refreshRecord();
    }

    public function finalizeAudit(): void
    {
        abort_unless($this->canFinalize(), 403);

        if ($this->incompleteChecklistCount() > 0) {
            $this->addError('finalize', 'Masih ada checklist yang belum diberi assessment.');

            Notification::make()
                ->danger()
                ->title('Audit belum bisa difinalisasi.')
                ->body('Lengkapi seluruh assessment checklist terlebih dahulu.')
                ->send();

            return;
        }

        $this->record->update([
            'status' => AmiAuditStatus::Finalized,
            'finalized_at' => now(),
            'finalized_by' => auth()->id(),
        ]);

        Notification::make()
            ->success()
            ->title('Audit berhasil difinalisasi.')
            ->send();

        $this->refreshRecord();
    }

    public function targetSummary(AmiChecklist $checklist): string
    {
        $indicator = $checklist->standardIndicator;

        if (! $indicator instanceof StandardIndicator) {
            return '-';
        }

        return trim(implode(' ', array_filter([
            $indicator->target_operator?->value,
            (float) $indicator->target_value,
            $indicator->target_unit,
        ]))) ?: '-';
    }

    public function realizationSummary(AmiChecklist $checklist): string
    {
        $achievement = $this->unitAchievement($checklist);

        if (! $achievement instanceof IndicatorAchievement) {
            return '-';
        }

        if ($achievement->realization_value !== null) {
            return trim(((string) (float) $achievement->realization_value).' '.($achievement->standard_indicator?->target_unit ?? ''));
        }

        return filled($achievement->realization_text)
            ? str($achievement->realization_text)->limit(120)->toString()
            : '-';
    }

    public function latestFinalReview(AmiChecklist $checklist): ?AchievementReview
    {
        return $this->unitAchievement($checklist)
            ?->reviews
            ->whereNotNull('reviewed_at')
            ->sortByDesc('reviewed_at')
            ->first();
    }

    public function unitAchievement(AmiChecklist $checklist): ?IndicatorAchievement
    {
        return $this->unitAssignment($checklist)?->latestAchievement;
    }

    public function unitAssignment(AmiChecklist $checklist): ?IndicatorUnitAssignment
    {
        $spmiPeriodId = $this->record->amiPeriod?->spmi_period_id;

        if ($spmiPeriodId === null) {
            return null;
        }

        return IndicatorUnitAssignment::query()
            ->with([
                'latestAchievement.evidences',
                'latestAchievement.reviews.reviewer',
                'standardIndicator.qualityStandard',
            ])
            ->where('unit_id', $this->record->auditee_unit_id)
            ->where('spmi_period_id', $spmiPeriodId)
            ->where('standard_indicator_id', $checklist->standard_indicator_id)
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

    public function evidenceName(AchievementEvidence $evidence): string
    {
        return $evidence->file_name
            ?: $evidence->external_url
            ?: basename((string) $evidence->file_path)
            ?: 'Bukti capaian';
    }

    public function roleLabel(): string
    {
        return $this->auditorRole()?->getLabel() ?? (auth()->user()?->isAuditor() ? '-' : 'Monitoring');
    }

    public function roleColor(): string|array|null
    {
        return $this->auditorRole()?->getColor() ?? 'gray';
    }

    public function canAssessChecklist(?AmiChecklist $checklist = null): bool
    {
        if ($this->record->status === AmiAuditStatus::Finalized) {
            return false;
        }

        $role = $this->auditorRole();

        return in_array($role, [AmiAuditorRole::Lead, AmiAuditorRole::Member], true)
            && ($checklist === null || (int) $checklist->ami_audit_id === (int) $this->record->id);
    }

    public function canCreateFindingFromChecklist(AmiChecklist $checklist): bool
    {
        return $this->canAssessChecklist($checklist)
            && $this->categoryFromAssessment($checklist->assessment_result) instanceof AmiFindingCategory;
    }

    public function canManageFinding(AmiFinding $finding): bool
    {
        return $this->record->status !== AmiAuditStatus::Finalized
            && (int) $finding->ami_audit_id === (int) $this->record->id
            && $this->canAssessChecklist();
    }

    public function canFinalize(): bool
    {
        if ($this->record->status === AmiAuditStatus::Finalized) {
            return false;
        }

        $user = auth()->user();

        return (bool) ($user?->isAdminLpm() || $user?->isSuperAdmin() || $this->auditorRole() === AmiAuditorRole::Lead);
    }

    public function incompleteChecklistCount(): int
    {
        return $this->record->checklists
            ->whereNull('assessment_result')
            ->count();
    }

    public function incompleteFindingCount(): int
    {
        return $this->record->findings
            ->filter(fn (AmiFinding $finding): bool => blank($finding->description) || blank($finding->category))
            ->count();
    }

    private function currentEditableChecklist(): AmiChecklist
    {
        abort_unless($this->editingChecklistId !== null, 404);

        $checklist = $this->findChecklist($this->editingChecklistId);

        abort_unless($this->canAssessChecklist($checklist), 403);

        return $checklist;
    }

    private function findChecklist(int $checklistId): AmiChecklist
    {
        return $this->record->checklists
            ->firstWhere('id', $checklistId)
            ?? abort(404);
    }

    private function findFinding(int $findingId): AmiFinding
    {
        return $this->record->findings
            ->firstWhere('id', $findingId)
            ?? abort(404);
    }

    /**
     * @return array<string, mixed>
     */
    private function findingPayload(?AmiChecklist $checklist): array
    {
        return [
            'ami_audit_id' => $this->record->id,
            'ami_checklist_id' => $checklist?->id,
            'standard_indicator_id' => $checklist?->standard_indicator_id,
            'category' => $this->findingCategory,
            'description' => $this->findingDescription,
            'root_cause' => $this->findingRootCause,
            'recommendation' => $this->findingRecommendation,
            'due_date' => $this->findingDueDate,
            'status' => $this->findingStatus,
        ];
    }

    private function categoryFromAssessment(?AmiAssessmentResult $assessment): ?AmiFindingCategory
    {
        return match ($assessment) {
            AmiAssessmentResult::Observation => AmiFindingCategory::Observation,
            AmiAssessmentResult::Minor => AmiFindingCategory::Minor,
            AmiAssessmentResult::Major => AmiFindingCategory::Major,
            AmiAssessmentResult::Ofi => AmiFindingCategory::Ofi,
            default => null,
        };
    }

    private function auditorRole(): ?AmiAuditorRole
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return null;
        }

        return $this->record->auditorAssignments
            ->first(fn ($assignment): bool => (int) $assignment->user_id === (int) $user->id)
            ?->role;
    }

    private function canViewAudit(): bool
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return false;
        }

        if ($user->isSuperAdmin() || $user->isAdminLpm()) {
            return true;
        }

        return $user->isAuditor() && $this->auditorRole() instanceof AmiAuditorRole;
    }

    private function refreshRecord(): void
    {
        $this->record->refresh()->load($this->recordRelations());
    }

    /**
     * @return array<int, string>
     */
    private function recordRelations(): array
    {
        return [
            'amiPeriod.spmiPeriod',
            'auditeeUnit',
            'finalizedBy',
            'auditorAssignments.user',
            'checklists.findings',
            'checklists.standardIndicator.qualityStandard',
            'findings.checklist.standardIndicator.qualityStandard',
            'findings.standardIndicator.qualityStandard',
            'findings.createdBy',
        ];
    }
}
