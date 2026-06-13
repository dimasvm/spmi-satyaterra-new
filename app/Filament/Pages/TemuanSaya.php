<?php

namespace App\Filament\Pages;

use App\Enums\AmiFindingCategory;
use App\Enums\AmiFindingStatus;
use App\Enums\CorrectiveActionStatus;
use App\Models\AmiFinding;
use App\Models\CorrectiveAction;
use App\Models\User;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use UnitEnum;

class TemuanSaya extends Page
{
    use WithFileUploads, WithPagination;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static string|UnitEnum|null $navigationGroup = 'Pengendalian';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Temuan Saya';

    protected static ?string $title = 'Temuan Saya';

    protected string $view = 'filament.pages.temuan-saya';

    public string $activeTab = 'all';

    public string $search = '';

    public bool $isTicketOpen = false;

    public ?int $selectedFindingId = null;

    public ?string $rootCauseAnalysis = null;

    public ?string $actionPlan = null;

    public ?int $picUserId = null;

    public ?string $targetDate = null;

    /** @var array<int, TemporaryUploadedFile> */
    public array $evidenceFiles = [];

    public ?string $externalUrl = null;

    public ?string $evidenceDescription = null;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return (bool) ($user?->isUnitPic() && $user->unit_id !== null && $user->can('corrective-actions.view'));
    }

    /**
     * @return array<string, string>
     */
    public function tabs(): array
    {
        return [
            'all' => 'Semua',
            AmiFindingStatus::Open->value => 'Terbuka',
            AmiFindingStatus::InProgress->value => 'Dalam Proses',
            AmiFindingStatus::WaitingVerification->value => 'Menunggu Verifikasi',
            AmiFindingStatus::NeedRevision->value => 'Perlu Revisi',
            AmiFindingStatus::Closed->value => 'Selesai',
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
        $query = $this->baseFindingQuery();

        return [
            'open' => (clone $query)->where('status', AmiFindingStatus::Open->value)->count(),
            'in_progress' => (clone $query)->where('status', AmiFindingStatus::InProgress->value)->count(),
            'waiting_verification' => (clone $query)->where('status', AmiFindingStatus::WaitingVerification->value)->count(),
            'need_revision' => (clone $query)->where('status', AmiFindingStatus::NeedRevision->value)->count(),
            'closed' => (clone $query)->where('status', AmiFindingStatus::Closed->value)->count(),
            'overdue' => (clone $query)
                ->where('status', '!=', AmiFindingStatus::Closed->value)
                ->whereDate('due_date', '<', today())
                ->count(),
        ];
    }

    /**
     * @return array<string, int>
     */
    public function tabCounts(): array
    {
        $query = $this->baseFindingQuery();

        return [
            'all' => (clone $query)->count(),
            AmiFindingStatus::Open->value => (clone $query)->where('status', AmiFindingStatus::Open->value)->count(),
            AmiFindingStatus::InProgress->value => (clone $query)->where('status', AmiFindingStatus::InProgress->value)->count(),
            AmiFindingStatus::WaitingVerification->value => (clone $query)->where('status', AmiFindingStatus::WaitingVerification->value)->count(),
            AmiFindingStatus::NeedRevision->value => (clone $query)->where('status', AmiFindingStatus::NeedRevision->value)->count(),
            AmiFindingStatus::Closed->value => (clone $query)->where('status', AmiFindingStatus::Closed->value)->count(),
        ];
    }

    /**
     * @return Paginator<int, AmiFinding>
     */
    public function findings(): Paginator
    {
        return $this->baseFindingQuery()
            ->when($this->activeTab !== 'all', fn (Builder $query): Builder => $query->where('status', $this->activeTab))
            ->when(filled($this->search), fn (Builder $query): Builder => $query->where(function (Builder $query): void {
                $query
                    ->where('finding_number', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%')
                    ->orWhereHas('audit.auditeeUnit', fn (Builder $unitQuery): Builder => $unitQuery->where('name', 'like', '%'.$this->search.'%'))
                    ->orWhereHas('standardIndicator', fn (Builder $indicatorQuery): Builder => $indicatorQuery
                        ->where('code', 'like', '%'.$this->search.'%')
                        ->orWhere('statement', 'like', '%'.$this->search.'%'));
            }))
            ->orderByDesc('id')
            ->simplePaginate(10);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openTicket(int $findingId): void
    {
        $finding = $this->findVisibleFinding($findingId);
        $correctiveAction = $finding->latestCorrectiveAction;

        $this->selectedFindingId = $finding->id;
        $this->rootCauseAnalysis = $correctiveAction?->root_cause_analysis;
        $this->actionPlan = $correctiveAction?->action_plan;
        $this->picUserId = $correctiveAction?->pic_user_id ?? auth()->id();
        $this->targetDate = $correctiveAction?->target_date?->format('Y-m-d');
        $this->evidenceFiles = [];
        $this->externalUrl = null;
        $this->evidenceDescription = null;
        $this->resetValidation();
        $this->isTicketOpen = true;
    }

    public function closeTicket(): void
    {
        $this->isTicketOpen = false;
        $this->selectedFindingId = null;
        $this->evidenceFiles = [];
        $this->resetValidation();
    }

    public function selectedFinding(): ?AmiFinding
    {
        if ($this->selectedFindingId === null) {
            return null;
        }

        return $this->findVisibleFinding($this->selectedFindingId);
    }

    public function saveDraft(): void
    {
        $this->persistCorrectiveAction(submit: false);

        Notification::make()
            ->success()
            ->title('Draf tindak lanjut disimpan.')
            ->send();

        $this->closeTicket();
    }

    public function submitVerification(): void
    {
        $this->persistCorrectiveAction(submit: true);

        Notification::make()
            ->success()
            ->title('Tindak lanjut dikirim untuk verifikasi.')
            ->send();

        $this->closeTicket();
    }

    /**
     * @return array<int, string>
     */
    public function picOptions(): array
    {
        $unitId = auth()->user()?->unit_id;

        return User::query()
            ->when($unitId !== null, fn (Builder $query): Builder => $query->where('unit_id', $unitId))
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    private function persistCorrectiveAction(bool $submit): void
    {
        $rules = [
            'rootCauseAnalysis' => ['nullable', 'string', 'max:5000'],
            'actionPlan' => ['required', 'string', 'max:5000'],
            'picUserId' => ['nullable', 'integer', 'exists:users,id'],
            'targetDate' => ['nullable', 'date'],
            'externalUrl' => ['nullable', 'url', 'max:255'],
            'evidenceDescription' => ['nullable', 'string', 'max:1000'],
            'evidenceFiles.*' => ['file', 'max:5120'],
        ];

        if ($submit) {
            $rules['rootCauseAnalysis'] = ['required', 'string', 'max:5000'];
            $rules['picUserId'] = ['required', 'integer', 'exists:users,id'];
            $rules['targetDate'] = ['required', 'date'];
        }

        $this->validate($rules);

        $finding = $this->selectedFinding();
        abort_unless($finding instanceof AmiFinding, 404);

        DB::transaction(function () use ($finding, $submit): void {
            $correctiveAction = $finding->latestCorrectiveAction;

            if (! $correctiveAction instanceof CorrectiveAction) {
                abort_unless(auth()->user()?->can('create', CorrectiveAction::class), 403);

                $correctiveAction = $finding->correctiveActions()->create([
                    'action_plan' => $this->actionPlan,
                    'root_cause_analysis' => $this->rootCauseAnalysis,
                    'pic_user_id' => $this->picUserId,
                    'target_date' => $this->targetDate,
                    'status' => CorrectiveActionStatus::Draft,
                ]);
            } else {
                abort_unless(auth()->user()?->can('update', $correctiveAction), 403);
                $correctiveAction->update([
                    'action_plan' => $this->actionPlan,
                    'root_cause_analysis' => $this->rootCauseAnalysis,
                    'pic_user_id' => $this->picUserId,
                    'target_date' => $this->targetDate,
                ]);
            }

            $this->storeEvidence($correctiveAction);

            if ($submit) {
                $correctiveAction->load('evidences');

                if (in_array($finding->category, [AmiFindingCategory::Minor, AmiFindingCategory::Major], true)
                    && ! $correctiveAction->evidences()->exists()) {
                    throw ValidationException::withMessages([
                        'evidenceFiles' => 'Minimal satu bukti wajib diunggah untuk temuan minor atau mayor.',
                    ]);
                }

                $correctiveAction->update([
                    'status' => CorrectiveActionStatus::Submitted,
                    'submitted_at' => now(),
                    'submitted_by' => auth()->id(),
                ]);

                $finding->update(['status' => AmiFindingStatus::WaitingVerification]);

                return;
            }

            if ($finding->status === AmiFindingStatus::Open) {
                $finding->update(['status' => AmiFindingStatus::InProgress]);
            }
        });
    }

    private function storeEvidence(CorrectiveAction $correctiveAction): void
    {
        collect($this->evidenceFiles)
            ->filter(fn (mixed $file): bool => $file instanceof TemporaryUploadedFile)
            ->each(function (TemporaryUploadedFile $file) use ($correctiveAction): void {
                $path = $file->store('corrective-action-evidences');

                $correctiveAction->evidences()->create([
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'description' => $this->evidenceDescription,
                    'uploaded_by' => auth()->id(),
                ]);
            });

        if (filled($this->externalUrl)) {
            $correctiveAction->evidences()->create([
                'external_url' => $this->externalUrl,
                'description' => $this->evidenceDescription,
                'uploaded_by' => auth()->id(),
            ]);
        }
    }

    private function findVisibleFinding(int $findingId): AmiFinding
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);

        return AmiFinding::query()
            ->with([
                'audit.auditeeUnit',
                'audit.amiPeriod',
                'standardIndicator.qualityStandard',
                'latestCorrectiveAction.evidences',
                'latestCorrectiveAction.reviews.reviewer',
                'latestCorrectiveAction.picUser',
            ])
            ->visibleToUser($user)
            ->findOrFail($findingId);
    }

    private function baseFindingQuery(): Builder
    {
        $user = auth()->user();

        return AmiFinding::query()
            ->with([
                'audit.auditeeUnit',
                'standardIndicator',
                'latestCorrectiveAction',
            ])
            ->when($user instanceof User, fn (Builder $query): Builder => $query->visibleToUser($user), fn (Builder $query): Builder => $query->whereRaw('1 = 0'));
    }
}
