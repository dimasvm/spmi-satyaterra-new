<?php

namespace App\Filament\Pages;

use App\Enums\AmiFindingStatus;
use App\Enums\CorrectiveActionReviewStatus;
use App\Enums\CorrectiveActionStatus;
use App\Models\CorrectiveAction;
use App\Models\User;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;
use UnitEnum;

class VerifikasiTindakLanjut extends Page
{
    use WithPagination;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Pengendalian';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Verifikasi Tindak Lanjut';

    protected static ?string $title = 'Verifikasi Tindak Lanjut';

    protected string $view = 'filament.pages.verifikasi-tindak-lanjut';

    public string $activeTab = 'submitted';

    public string $search = '';

    public bool $isDetailOpen = false;

    public ?int $selectedCorrectiveActionId = null;

    public ?string $reviewNotes = null;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return (bool) (($user?->isAuditor() || $user?->isAdminLpm() || $user?->isSuperAdmin())
            && $user?->can('corrective-actions.review'));
    }

    /**
     * @return array<string, string>
     */
    public function tabs(): array
    {
        return [
            'submitted' => 'Menunggu Verifikasi',
            'in_review' => 'Ditinjau',
            'all' => 'Semua',
        ];
    }

    public function setTab(string $tab): void
    {
        if (array_key_exists($tab, $this->tabs())) {
            $this->activeTab = $tab;
            $this->resetPage();
        }
    }

    /**
     * @return array<string, int>
     */
    public function stats(): array
    {
        $query = $this->baseQuery();

        return [
            'submitted' => (clone $query)->where('status', CorrectiveActionStatus::Submitted->value)->count(),
            'in_review' => (clone $query)->where('status', CorrectiveActionStatus::InReview->value)->count(),
            'overdue' => (clone $query)
                ->where(function (Builder $query): void {
                    $query
                        ->whereDate('target_date', '<', today())
                        ->orWhereHas('finding', fn (Builder $findingQuery): Builder => $findingQuery->whereDate('due_date', '<', today()));
                })
                ->count(),
        ];
    }

    /**
     * @return array<string, int>
     */
    public function tabCounts(): array
    {
        $query = $this->baseQuery();

        return [
            'submitted' => (clone $query)->where('status', CorrectiveActionStatus::Submitted->value)->count(),
            'in_review' => (clone $query)->where('status', CorrectiveActionStatus::InReview->value)->count(),
            'all' => (clone $query)->count(),
        ];
    }

    /**
     * @return Paginator<int, CorrectiveAction>
     */
    public function correctiveActions(): Paginator
    {
        return $this->baseQuery()
            ->when($this->activeTab !== 'all', fn (Builder $query): Builder => $query->where('status', $this->activeTab))
            ->when(filled($this->search), fn (Builder $query): Builder => $query->where(function (Builder $query): void {
                $query
                    ->where('action_plan', 'like', '%'.$this->search.'%')
                    ->orWhereHas('finding', fn (Builder $findingQuery): Builder => $findingQuery
                        ->where('finding_number', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%'))
                    ->orWhereHas('finding.audit.auditeeUnit', fn (Builder $unitQuery): Builder => $unitQuery->where('name', 'like', '%'.$this->search.'%'));
            }))
            ->orderByDesc('id')
            ->simplePaginate(10);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openDetail(int $correctiveActionId): void
    {
        $correctiveAction = $this->findReviewableAction($correctiveActionId);

        if ($correctiveAction->status === CorrectiveActionStatus::Submitted) {
            $correctiveAction->update(['status' => CorrectiveActionStatus::InReview]);
        }

        $this->selectedCorrectiveActionId = $correctiveAction->id;
        $this->reviewNotes = null;
        $this->resetValidation();
        $this->isDetailOpen = true;
    }

    public function closeDetail(): void
    {
        $this->selectedCorrectiveActionId = null;
        $this->reviewNotes = null;
        $this->isDetailOpen = false;
        $this->resetValidation();
    }

    public function selectedCorrectiveAction(): ?CorrectiveAction
    {
        if ($this->selectedCorrectiveActionId === null) {
            return null;
        }

        return $this->findReviewableAction($this->selectedCorrectiveActionId);
    }

    public function accept(): void
    {
        $correctiveAction = $this->currentReviewableAction();

        DB::transaction(function () use ($correctiveAction): void {
            $correctiveAction->reviews()->create([
                'reviewer_id' => auth()->id(),
                'status' => CorrectiveActionReviewStatus::Accepted,
                'notes' => $this->reviewNotes,
                'reviewed_at' => now(),
            ]);

            $correctiveAction->update(['status' => CorrectiveActionStatus::Accepted]);
            $correctiveAction->finding?->update(['status' => AmiFindingStatus::Closed]);
        });

        Notification::make()
            ->success()
            ->title('Tindak lanjut diterima dan temuan ditutup.')
            ->send();

        $this->closeDetail();
    }

    public function requestRevision(): void
    {
        $this->validate([
            'reviewNotes' => ['required', 'string', 'max:2000'],
        ]);

        $correctiveAction = $this->currentReviewableAction();

        DB::transaction(function () use ($correctiveAction): void {
            $correctiveAction->reviews()->create([
                'reviewer_id' => auth()->id(),
                'status' => CorrectiveActionReviewStatus::NeedRevision,
                'notes' => $this->reviewNotes,
                'reviewed_at' => now(),
            ]);

            $correctiveAction->update(['status' => CorrectiveActionStatus::NeedRevision]);
            $correctiveAction->finding?->update(['status' => AmiFindingStatus::NeedRevision]);
        });

        Notification::make()
            ->warning()
            ->title('Revisi tindak lanjut diminta.')
            ->send();

        $this->closeDetail();
    }

    private function currentReviewableAction(): CorrectiveAction
    {
        abort_unless($this->selectedCorrectiveActionId !== null, 404);

        $correctiveAction = $this->findReviewableAction($this->selectedCorrectiveActionId);

        abort_unless(in_array($correctiveAction->status, [
            CorrectiveActionStatus::Submitted,
            CorrectiveActionStatus::InReview,
        ], true), 403);

        return $correctiveAction;
    }

    private function findReviewableAction(int $correctiveActionId): CorrectiveAction
    {
        return $this->baseQuery()->findOrFail($correctiveActionId);
    }

    private function baseQuery(): Builder
    {
        $user = auth()->user();

        return CorrectiveAction::query()
            ->with([
                'finding.audit.auditeeUnit',
                'finding.standardIndicator.qualityStandard',
                'picUser',
                'evidences',
                'reviews.reviewer',
            ])
            ->whereIn('status', [
                CorrectiveActionStatus::Submitted->value,
                CorrectiveActionStatus::InReview->value,
            ])
            ->when($user instanceof User, fn (Builder $query): Builder => $query->visibleToUser($user), fn (Builder $query): Builder => $query->whereRaw('1 = 0'));
    }
}
