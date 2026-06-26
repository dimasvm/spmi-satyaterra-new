<?php

namespace App\Filament\Pages;

use App\Enums\AchievementReviewStatus;
use App\Enums\EvidenceFileType;
use App\Enums\IndicatorAssignmentStatus;
use App\Enums\SubmissionStatus;
use App\Models\AchievementEvidence;
use App\Models\AchievementReview;
use App\Models\IndicatorAchievement;
use App\Models\SpmiPeriod;
use App\Models\SystemSetting;
use App\Models\Unit;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Livewire\WithPagination;
use UnitEnum;

class InboxValidasiCapaian extends Page
{
    use WithPagination;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Pelaksanaan';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Inbox Validasi Capaian';

    protected static ?string $title = 'Inbox Validasi Capaian';

    protected string $view = 'filament.pages.inbox-validasi-capaian';

    public ?int $selectedSpmiPeriodId = null;

    public ?int $selectedUnitId = null;

    public string $activeTab = 'submitted';

    public bool $isReviewModalOpen = false;

    public ?int $reviewingAchievementId = null;

    public ?string $reviewNotes = null;

    public string $search = '';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return (bool) ($user?->isSuperAdmin() || $user?->isAdminLpm() || $user?->can('indicator-achievements.review'));
    }

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);

        $user = auth()->user();

        $this->selectedSpmiPeriodId = SpmiPeriod::active()->value('id')
            ?? SpmiPeriod::query()->latest('start_date')->value('id');

        if ($user?->isPicMonitoring() && $user->unit_id !== null) {
            $this->selectedUnitId = $user->unit_id;
        }

        $validationRequired = (bool) SystemSetting::get('achievement_validation_required', true);
        if (! $validationRequired) {
            $this->activeTab = 'validated';
        }
    }

    /**
     * @return array<int|string, string>
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
        $query = Unit::query();

        $user = auth()->user();

        if ($user->unit_id !== null) {
            $allowedUnitIds = $user->unit?->getAllDescendantIds() ?? [];
            $query->whereIn('id', $allowedUnitIds);
        }

        return $query->orderBy('name')
            ->pluck('name', 'id')->all();
    }

    /**
     * @return array<string, string>
     */
    public function tabs(): array
    {
        return [
            'submitted' => 'Menunggu Validasi',
            'returned' => 'Dikembalikan',
            'validated' => 'Tervalidasi',
            'all' => 'Semua',
        ];
    }

    public function setTab(string $tab): void
    {
        if (array_key_exists($tab, $this->tabs())) {
            $this->activeTab = $tab;
        }
    }

    /**
     * @return array<string, int>
     */
    public function stats(): array
    {
        $query = $this->baseAchievementQuery();

        return [
            'submitted' => (clone $query)->where('submission_status', SubmissionStatus::Submitted->value)->count(),
            'returned' => (clone $query)->where('submission_status', SubmissionStatus::Returned->value)->count(),
            'validated' => (clone $query)->where('submission_status', SubmissionStatus::Validated->value)->count(),
            'rejected' => AchievementReview::query()
                ->where('status', AchievementReviewStatus::Rejected->value)
                ->whereHas('achievement', fn (Builder $achievementQuery): Builder => $this->applyPeriodFilter($achievementQuery))
                ->count(),
        ];
    }

    /**
     * @return array<string, int>
     */
    public function tabCounts(): array
    {
        return [
            'submitted' => (clone $this->baseAchievementQuery())->where('submission_status', SubmissionStatus::Submitted->value)->count(),
            'returned' => (clone $this->baseAchievementQuery())->where('submission_status', SubmissionStatus::Returned->value)->count(),
            'validated' => (clone $this->baseAchievementQuery())->where('submission_status', SubmissionStatus::Validated->value)->count(),
            'all' => $this->baseAchievementQuery()->count(),
        ];
    }

    /**
     * @return Paginator<int, IndicatorAchievement>
     */
    public function achievements(): Paginator
    {
        return $this->baseAchievementQuery()
            ->when($this->activeTab !== 'all', fn (Builder $query): Builder => $query->where('submission_status', $this->activeTab))
            ->when(filled($this->search), fn (Builder $query): Builder => $query->where(function (Builder $query): void {
                $query
                    ->whereHas('assignment.unit', fn (Builder $unitQuery): Builder => $unitQuery->where('name', 'like', '%'.$this->search.'%'))
                    ->orWhereHas('assignment.standardIndicator', fn (Builder $indicatorQuery): Builder => $indicatorQuery
                        ->where('code', 'like', '%'.$this->search.'%')
                        ->orWhere('statement', 'like', '%'.$this->search.'%'))
                    ->orWhereHas('assignment.standardIndicator.qualityStandard', fn (Builder $standardQuery): Builder => $standardQuery->where('name', 'like', '%'.$this->search.'%'));
            }))
            ->orderByRaw('submitted_at is null')
            ->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->simplePaginate(10);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openReview(int $achievementId): void
    {
        $achievement = $this->findVisibleAchievement($achievementId);

        $this->reviewingAchievementId = $achievement->id;
        $this->reviewNotes = null;
        $this->resetValidation();
        $this->isReviewModalOpen = true;
    }

    public function closeReview(): void
    {
        $this->isReviewModalOpen = false;
        $this->reviewingAchievementId = null;
        $this->reviewNotes = null;
        $this->resetValidation();
    }

    public function reviewingAchievement(): ?IndicatorAchievement
    {
        if ($this->reviewingAchievementId === null) {
            return null;
        }

        return $this->findVisibleAchievement($this->reviewingAchievementId);
    }

    public function validateAchievement(): void
    {
        $this->review(
            reviewStatus: AchievementReviewStatus::Validated,
            submissionStatus: SubmissionStatus::Validated,
            assignmentStatus: IndicatorAssignmentStatus::Validated,
            notesRequired: false,
            successTitle: 'Capaian berhasil divalidasi.',
        );
    }

    public function returnAchievement(): void
    {
        $this->review(
            reviewStatus: AchievementReviewStatus::Returned,
            submissionStatus: SubmissionStatus::Returned,
            assignmentStatus: IndicatorAssignmentStatus::Returned,
            notesRequired: true,
            successTitle: 'Capaian dikembalikan untuk revisi.',
        );
    }

    public function rejectAchievement(): void
    {
        $this->review(
            reviewStatus: AchievementReviewStatus::Rejected,
            submissionStatus: SubmissionStatus::Returned,
            assignmentStatus: IndicatorAssignmentStatus::Returned,
            notesRequired: true,
            successTitle: 'Capaian ditolak.',
        );
    }

    public function targetSummary(?IndicatorAchievement $achievement): string
    {
        $indicator = $achievement?->standard_indicator;

        if ($indicator === null) {
            return '-';
        }

        return trim(implode(' ', array_filter([
            $indicator->target_operator?->value,
            (float) $indicator->target_value,
            $indicator->target_unit,
        ]))) ?: '-';
    }

    public function realizationSummary(?IndicatorAchievement $achievement): string
    {
        if (! $achievement instanceof IndicatorAchievement) {
            return '-';
        }

        if ($achievement->realization_value !== null) {
            return trim(((string) (float) $achievement->realization_value).' '.($achievement->standard_indicator?->target_unit ?? ''));
        }

        return filled($achievement->realization_text)
            ? str($achievement->realization_text)->limit(80)->toString()
            : '-';
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

    private function review(
        AchievementReviewStatus $reviewStatus,
        SubmissionStatus $submissionStatus,
        IndicatorAssignmentStatus $assignmentStatus,
        bool $notesRequired,
        string $successTitle,
    ): void {
        abort_unless(static::canAccess(), 403);

        $validationRequired = (bool) SystemSetting::get('achievement_validation_required', true);
        abort_unless($validationRequired, 403, 'Validasi dinonaktifkan.');

        $rules = ['reviewNotes' => ['nullable', 'string', 'max:1000']];

        if ($notesRequired) {
            $rules['reviewNotes'] = ['required', 'string', 'max:1000'];
        }

        $this->validate($rules);

        $achievement = $this->currentReviewableAchievement();

        DB::transaction(function () use ($achievement, $reviewStatus, $submissionStatus, $assignmentStatus): void {
            $this->recordReview($achievement, $reviewStatus, $this->reviewNotes);

            $achievement->update([
                'submission_status' => $submissionStatus,
            ]);

            $achievement->assignment()->update([
                'status' => $assignmentStatus,
            ]);
        });

        Notification::make()
            ->success()
            ->title($successTitle)
            ->send();

        $this->closeReview();
    }

    private function recordReview(IndicatorAchievement $achievement, AchievementReviewStatus $reviewStatus, ?string $notes): void
    {
        $reviewData = [
            'reviewer_id' => auth()->id(),
            'status' => $reviewStatus,
            'notes' => $notes,
            'reviewed_at' => now(),
        ];

        $pendingReview = $achievement->reviews()
            ->where('status', AchievementReviewStatus::Pending)
            ->oldest()
            ->lockForUpdate()
            ->first();

        if ($pendingReview instanceof AchievementReview) {
            $pendingReview->update($reviewData);

            return;
        }

        $achievement->reviews()->create($reviewData);
    }

    private function currentReviewableAchievement(): IndicatorAchievement
    {
        abort_unless($this->reviewingAchievementId !== null, 404);

        return $this->baseAchievementQuery()
            ->whereKey($this->reviewingAchievementId)
            ->where('submission_status', SubmissionStatus::Submitted->value)
            ->firstOrFail();
    }

    private function findVisibleAchievement(int $achievementId): IndicatorAchievement
    {
        return $this->baseAchievementQuery()
            ->whereKey($achievementId)
            ->firstOrFail();
    }

    private function baseAchievementQuery(): Builder
    {
        $query = IndicatorAchievement::query()
            ->with([
                'assignment.spmiPeriod',
                'assignment.standardIndicator.qualityStandard',
                'assignment.unit',
                'evidences.uploadedBy',
                'reviews.reviewer',
                'latestReview.reviewer',
                'submittedBy',
            ])
            ->withCount('evidences')
            ->whereIn('submission_status', [
                SubmissionStatus::Submitted->value,
                SubmissionStatus::Returned->value,
                SubmissionStatus::Validated->value,
            ]);

        return $this->applyPeriodFilter($query);
    }

    private function applyPeriodFilter(Builder $query): Builder
    {
        $user = auth()->user();

        return $query
            ->when($this->selectedSpmiPeriodId !== null, fn (Builder $periodQuery): Builder => $periodQuery
                ->whereHas('assignment', fn (Builder $assignmentQuery): Builder => $assignmentQuery->where('spmi_period_id', $this->selectedSpmiPeriodId)))
            ->when($this->selectedUnitId !== null, fn (Builder $unitQuery): Builder => $unitQuery
                ->whereHas('assignment', fn (Builder $assignmentQuery): Builder => $assignmentQuery->where('unit_id', $this->selectedUnitId)))
            ->when($this->selectedUnitId === null && $user?->isPicMonitoring() && $user->unit_id !== null, function (Builder $picQuery) use ($user): Builder {
                $allowedUnitIds = $user->unit?->getAllDescendantIds() ?? [];

                return $picQuery->whereHas('assignment', fn (Builder $assignmentQuery): Builder => $assignmentQuery->whereIn('unit_id', $allowedUnitIds));
            });
    }
}
