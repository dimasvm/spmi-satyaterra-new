<?php

namespace App\Filament\Pages;

use App\Enums\AchievementReviewStatus;
use App\Enums\AchievementStatus;
use App\Enums\EvidenceFileType;
use App\Enums\IndicatorAssignmentStatus;
use App\Enums\StandardIndicatorType;
use App\Enums\SubmissionStatus;
use App\Filament\Resources\IndicatorAchievements\IndicatorAchievementResource;
use App\Models\IndicatorAchievement;
use App\Models\IndicatorUnitAssignment;
use App\Models\SpmiPeriod;
use App\Models\StandardIndicator;
use App\Models\Unit;
use App\Models\User;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use UnitEnum;

class CapaianIndikatorSaya extends Page
{
    use WithFileUploads, WithPagination;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowTrendingUp;

    protected static string|UnitEnum|null $navigationGroup = 'Pelaksanaan';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Capaian Indikator Saya';

    protected static ?string $title = 'Capaian Indikator Saya';

    protected string $view = 'filament.pages.capaian-indikator-saya';

    protected ?string $heading = '';

    public ?int $selectedSpmiPeriodId = null;

    public ?int $selectedUnitId = null;

    public string $activeTab = 'all';

    public string $search = '';

    public bool $isAchievementModalOpen = false;

    public ?int $editingAssignmentId = null;

    public ?int $editingAchievementId = null;

    public ?string $realizationValue = null;

    public ?string $realizationText = null;

    public ?string $achievementStatus = null;

    public ?string $notes = null;

    /** @var array<int, TemporaryUploadedFile> */
    public array $evidenceFiles = [];

    public ?string $externalUrl = null;

    public ?string $evidenceDescription = null;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return (bool) ($user?->isUnitPic() || $user?->isAdminLpm() || $user?->isSuperAdmin());
    }

    public function mount(): void
    {
        $this->selectedSpmiPeriodId = SpmiPeriod::active()->value('id')
            ?? SpmiPeriod::query()->latest('start_date')->value('id');
    }

    /**
     * @return array<string, string>
     */
    public function periodOptions(): array
    {
        return SpmiPeriod::query()
            ->orderByDesc('start_date')
            ->pluck('name', 'id')
            ->all();
    }

    /**
     * @return array<int|string, string>
     */
    public function unitOptions(): array
    {
        $user = auth()->user();

        if ($user && $user->isUnitPic() && $user->unit_id !== null && ! ($user->isAdminLpm() || $user->isSuperAdmin())) {
            return Unit::whereKey($user->unit_id)->pluck('name', 'id')->all();
        }

        return Unit::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function statusTabs(): array
    {
        return [
            'all' => 'Semua',
            'empty' => 'Belum Diisi',
            'draft' => 'Draf',
            'submitted' => 'Menunggu Validasi',
            'returned' => 'Dikembalikan',
            'validated' => 'Tervalidasi',
            'not_achieved' => 'Belum Tercapai',
        ];
    }

    public function setTab(string $tab): void
    {
        if (array_key_exists($tab, $this->statusTabs())) {
            $this->activeTab = $tab;
            $this->resetPage();
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function headerSummary(): array
    {
        $assignments = $this->allAssignments();
        $total = $assignments->count();
        $completed = $assignments->filter(fn (IndicatorUnitAssignment $assignment): bool => $assignment->latestAchievement?->submission_status === SubmissionStatus::Validated)->count();
        $filled = $assignments->filter(fn (IndicatorUnitAssignment $assignment): bool => $assignment->latestAchievement !== null)->count();
        $nearestDeadline = $assignments
            ->filter(fn (IndicatorUnitAssignment $assignment): bool => $assignment->due_date !== null && $assignment->latestAchievement?->submission_status !== SubmissionStatus::Validated)
            ->sortBy('due_date')
            ->first();

        return [
            'unit' => $this->unitLabel(),
            'period' => $this->selectedPeriod()?->name ?? 'Belum ada periode aktif',
            'total' => $total,
            'filled' => $filled,
            'completed' => $completed,
            'progress' => $total > 0 ? (int) round(($completed / $total) * 100) : 0,
            'nearest_deadline' => $nearestDeadline?->due_date?->format('d M Y'),
            'nearest_deadline_warning' => $nearestDeadline?->due_date !== null && $nearestDeadline->due_date->isBefore(now()->addDays(7)),
        ];
    }

    /**
     * @return Paginator<int, IndicatorUnitAssignment>
     */
    public function assignments(): Paginator
    {
        return $this->filteredAssignmentQuery()
            ->orderByDesc('id')
            ->simplePaginate(10);
    }

    /**
     * @return array<string, int>
     */
    public function tabCounts(): array
    {
        $counts = [];

        foreach (array_keys($this->statusTabs()) as $tab) {
            $counts[$tab] = $this->applyTabFilter((clone $this->assignmentQuery()), $tab)->count();
        }

        return $counts;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openAchievementForm(int $assignmentId): void
    {
        $assignment = $this->findVisibleAssignment($assignmentId);
        $achievement = $this->resolveEditableAchievement($assignment);

        $this->editingAssignmentId = $assignment->id;
        $this->editingAchievementId = $achievement->id;
        $this->realizationValue = $achievement->realization_value !== null ? (string) (float) $achievement->realization_value : null;
        $this->realizationText = $achievement->realization_text;
        $this->achievementStatus = $achievement->achievement_status?->value;
        $this->notes = $achievement->notes;
        $this->evidenceFiles = [];
        $this->externalUrl = null;
        $this->evidenceDescription = null;
        $this->resetValidation();

        $this->isAchievementModalOpen = true;
    }

    public function closeAchievementForm(): void
    {
        $this->isAchievementModalOpen = false;
        $this->editingAssignmentId = null;
        $this->editingAchievementId = null;
        $this->evidenceFiles = [];
        $this->resetValidation();
    }

    public function saveDraft(): void
    {
        $this->persistAchievement(SubmissionStatus::Draft);

        Notification::make()
            ->title('Draf capaian disimpan.')
            ->success()
            ->send();

        $this->closeAchievementForm();
    }

    public function submitAchievement(): void
    {
        $achievement = $this->currentAchievement();
        $assignment = $this->currentAssignment();

        if ($assignment->standardIndicator?->evidence_required && ! $this->hasAnyEvidence($achievement)) {
            $this->addError('evidenceFiles', 'Bukti wajib diunggah atau ditautkan sebelum submit.');

            return;
        }

        $this->persistAchievement(SubmissionStatus::Submitted);

        Notification::make()
            ->title('Capaian indikator dikirim ke LPM.')
            ->success()
            ->send();

        $this->closeAchievementForm();
    }

    public function viewReview(int $assignmentId): mixed
    {
        $assignment = $this->findVisibleAssignment($assignmentId);
        $achievement = $assignment->latestAchievement;

        if (! $achievement instanceof IndicatorAchievement) {
            Notification::make()
                ->title('Capaian belum diisi.')
                ->warning()
                ->send();

            return null;
        }

        return redirect()->to(IndicatorAchievementResource::getUrl('view', [
            'record' => $achievement,
        ]));
    }

    public function targetSummary(?IndicatorUnitAssignment $assignment = null): string
    {
        $indicator = $assignment?->standardIndicator ?? $this->currentAssignment()?->standardIndicator;

        if (! $indicator instanceof StandardIndicator) {
            return '-';
        }

        return trim(implode(' ', array_filter([
            $indicator->target_operator?->value,
            (float) $indicator->target_value,
            $indicator->target_unit,
        ]))) ?: '-';
    }

    public function formIndicatorType(): ?StandardIndicatorType
    {
        return $this->formAssignment()?->standardIndicator?->indicator_type;
    }

    public function formAssignment(): ?IndicatorUnitAssignment
    {
        if ($this->editingAssignmentId === null) {
            return null;
        }

        return $this->findVisibleAssignment($this->editingAssignmentId);
    }

    /**
     * @return Collection<int, IndicatorUnitAssignment>
     */
    private function allAssignments(): Collection
    {
        return $this->assignmentQuery()
            ->orderByRaw('due_date is null')
            ->orderBy('due_date')
            ->get();
    }

    private function filteredAssignmentQuery(): Builder
    {
        return $this->applyTabFilter($this->assignmentQuery(), $this->activeTab)
            ->when(filled($this->search), fn (Builder $query): Builder => $query->where(function (Builder $query): void {
                $query
                    ->whereHas('unit', fn (Builder $unitQuery): Builder => $unitQuery->where('name', 'like', '%'.$this->search.'%'))
                    ->orWhereHas('standardIndicator', fn (Builder $indicatorQuery): Builder => $indicatorQuery
                        ->where('code', 'like', '%'.$this->search.'%')
                        ->orWhere('statement', 'like', '%'.$this->search.'%'))
                    ->orWhereHas('standardIndicator.qualityStandard', fn (Builder $standardQuery): Builder => $standardQuery->where('name', 'like', '%'.$this->search.'%'));
            }));
    }

    private function assignmentQuery(): Builder
    {
        $query = IndicatorUnitAssignment::query()
            ->with([
                'spmiPeriod',
                'unit',
                'standardIndicator.qualityStandard',
                'latestAchievement.evidences',
                'latestAchievement.latestReview.reviewer',
            ])
            ->when($this->selectedSpmiPeriodId !== null, fn (Builder $periodQuery): Builder => $periodQuery->where('spmi_period_id', $this->selectedSpmiPeriodId));

        $user = auth()->user();

        if (! $user instanceof User) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isAdminLpm() || $user->isSuperAdmin()) {
            return $query->when($this->selectedUnitId !== null, fn (Builder $unitQuery): Builder => $unitQuery->where('unit_id', $this->selectedUnitId));
        }

        if ($user->isUnitPic() && $user->unit_id !== null) {
            return $query->where('unit_id', $user->unit_id);
        }

        return $query->whereRaw('1 = 0');
    }

    private function selectedPeriod(): ?SpmiPeriod
    {
        return $this->selectedSpmiPeriodId !== null
            ? SpmiPeriod::query()->find($this->selectedSpmiPeriodId)
            : null;
    }

    private function unitLabel(): string
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return '-';
        }

        if ($user->isAdminLpm() || $user->isSuperAdmin()) {
            if ($this->selectedUnitId !== null) {
                return Unit::find($this->selectedUnitId)?->name ?? 'Semua Unit';
            }

            return 'Semua Unit';
        }

        return $user->unit?->name ?? 'Unit belum diatur';
    }

    private function applyTabFilter(Builder $query, string $tab): Builder
    {
        return match ($tab) {
            'empty' => $query->whereDoesntHave('achievements'),
            'draft' => $query->whereHas('latestAchievement', fn (Builder $achievementQuery): Builder => $achievementQuery->where('submission_status', SubmissionStatus::Draft->value)),
            'submitted' => $query->whereHas('latestAchievement', fn (Builder $achievementQuery): Builder => $achievementQuery->where('submission_status', SubmissionStatus::Submitted->value)),
            'returned' => $query->whereHas('latestAchievement', fn (Builder $achievementQuery): Builder => $achievementQuery->where('submission_status', SubmissionStatus::Returned->value)),
            'validated' => $query->whereHas('latestAchievement', fn (Builder $achievementQuery): Builder => $achievementQuery->where('submission_status', SubmissionStatus::Validated->value)),
            'not_achieved' => $query->whereHas('latestAchievement', fn (Builder $achievementQuery): Builder => $achievementQuery->where('achievement_status', AchievementStatus::NotAchieved->value)),
            default => $query,
        };
    }

    private function findVisibleAssignment(int $assignmentId): IndicatorUnitAssignment
    {
        return $this->assignmentQuery()->whereKey($assignmentId)->firstOrFail();
    }

    private function resolveEditableAchievement(IndicatorUnitAssignment $assignment): IndicatorAchievement
    {
        $achievement = $assignment->achievements()
            ->whereIn('submission_status', [
                SubmissionStatus::Draft->value,
                SubmissionStatus::Returned->value,
            ])
            ->latest()
            ->first();

        if (! $achievement instanceof IndicatorAchievement) {
            $latestAchievement = $assignment->achievements()->latest()->first();

            if ($latestAchievement instanceof IndicatorAchievement && ! in_array($latestAchievement->submission_status, [
                SubmissionStatus::Draft,
                SubmissionStatus::Returned,
            ], true)) {
                return $latestAchievement;
            }

            $achievement = $assignment->achievements()->create([
                'submission_status' => SubmissionStatus::Draft,
            ]);
        }

        if ($assignment->status === IndicatorAssignmentStatus::Assigned) {
            $assignment->update([
                'status' => IndicatorAssignmentStatus::InProgress,
            ]);
        }

        return $achievement;
    }

    private function persistAchievement(SubmissionStatus $submissionStatus): void
    {
        $this->validate($this->achievementRules());

        $achievement = $this->currentAchievement();
        $assignment = $this->currentAssignment();
        $achievementStatus = $this->resolveAchievementStatus($assignment->standardIndicator);

        DB::transaction(function () use ($achievement, $assignment, $achievementStatus, $submissionStatus): void {
            $achievement->update([
                'realization_value' => filled($this->realizationValue) ? $this->realizationValue : null,
                'realization_text' => $this->realizationText,
                'achievement_status' => $achievementStatus,
                'notes' => $this->notes,
                'submission_status' => $submissionStatus,
                'submitted_at' => $submissionStatus === SubmissionStatus::Submitted ? now() : $achievement->submitted_at,
                'submitted_by' => $submissionStatus === SubmissionStatus::Submitted ? auth()->id() : $achievement->submitted_by,
            ]);

            $this->persistEvidence($achievement);

            $assignment->update([
                'status' => $submissionStatus === SubmissionStatus::Submitted
                    ? IndicatorAssignmentStatus::Submitted
                    : IndicatorAssignmentStatus::InProgress,
            ]);

            if ($submissionStatus === SubmissionStatus::Submitted) {
                $achievement->reviews()->create([
                    'reviewer_id' => null,
                    'status' => AchievementReviewStatus::Pending,
                    'notes' => null,
                    'reviewed_at' => null,
                ]);
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function achievementRules(): array
    {
        return [
            'realizationValue' => ['nullable', 'numeric', 'min:0'],
            'realizationText' => ['nullable', 'string'],
            'achievementStatus' => ['nullable', 'string'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'externalUrl' => ['nullable', 'url', 'max:255'],
            'evidenceDescription' => ['nullable', 'string', 'max:1000'],
            'evidenceFiles' => ['array'],
            'evidenceFiles.*' => ['file', 'max:5120'],
        ];
    }

    private function currentAssignment(): IndicatorUnitAssignment
    {
        abort_unless($this->editingAssignmentId !== null, 404);

        return $this->findVisibleAssignment($this->editingAssignmentId);
    }

    private function currentAchievement(): IndicatorAchievement
    {
        abort_unless($this->editingAchievementId !== null, 404);

        return IndicatorAchievement::query()
            ->whereKey($this->editingAchievementId)
            ->whereHas('assignment', fn (Builder $query): Builder => $query->whereKey($this->editingAssignmentId))
            ->firstOrFail();
    }

    private function resolveAchievementStatus(?StandardIndicator $indicator): ?AchievementStatus
    {
        if ($indicator instanceof StandardIndicator && $this->realizationValue !== null && $this->realizationValue !== '') {
            $realizationValue = (float) $this->realizationValue;
            $targetValue = (float) $indicator->target_value;

            return match ($indicator->target_operator?->value) {
                '>=' => $realizationValue >= $targetValue ? AchievementStatus::Achieved : AchievementStatus::NotAchieved,
                '<=' => $realizationValue <= $targetValue ? AchievementStatus::Achieved : AchievementStatus::NotAchieved,
                '>' => $realizationValue > $targetValue ? AchievementStatus::Achieved : AchievementStatus::NotAchieved,
                '<' => $realizationValue < $targetValue ? AchievementStatus::Achieved : AchievementStatus::NotAchieved,
                '=' => $realizationValue === $targetValue ? AchievementStatus::Achieved : AchievementStatus::NotAchieved,
                default => AchievementStatus::tryFrom((string) $this->achievementStatus),
            };
        }

        return AchievementStatus::tryFrom((string) $this->achievementStatus);
    }

    private function hasAnyEvidence(IndicatorAchievement $achievement): bool
    {
        return $achievement->evidences()->exists()
            || filled($this->externalUrl)
            || count($this->evidenceFiles) > 0;
    }

    private function persistEvidence(IndicatorAchievement $achievement): void
    {
        if (filled($this->externalUrl)) {
            $achievement->evidences()->create([
                'file_type' => EvidenceFileType::Link,
                'external_url' => $this->externalUrl,
                'description' => $this->evidenceDescription,
                'uploaded_by' => auth()->id(),
            ]);
        }

        foreach ($this->evidenceFiles as $file) {
            $achievement->evidences()->create([
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $file->store('achievement-evidences'),
                'file_type' => $this->fileTypeForUpload($file),
                'description' => $this->evidenceDescription,
                'uploaded_by' => auth()->id(),
            ]);
        }
    }

    private function fileTypeForUpload(TemporaryUploadedFile $file): EvidenceFileType
    {
        return match (strtolower($file->getClientOriginalExtension())) {
            'pdf' => EvidenceFileType::Pdf,
            'docx' => EvidenceFileType::Docx,
            'xlsx' => EvidenceFileType::Xlsx,
            'jpg', 'jpeg', 'png', 'webp' => EvidenceFileType::Image,
            default => EvidenceFileType::Link,
        };
    }
}
