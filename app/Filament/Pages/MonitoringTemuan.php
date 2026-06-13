<?php

namespace App\Filament\Pages;

use App\Enums\AmiFindingCategory;
use App\Enums\AmiFindingStatus;
use App\Models\AmiFinding;
use App\Models\AmiPeriod;
use App\Models\Unit;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Livewire\WithPagination;
use UnitEnum;

class MonitoringTemuan extends Page
{
    use WithPagination;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBarSquare;

    protected static string|UnitEnum|null $navigationGroup = 'Pengendalian';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Monitoring Temuan';

    protected static ?string $title = 'Monitoring Temuan';

    protected string $view = 'filament.pages.monitoring-temuan';

    public ?int $selectedUnitId = null;

    public ?int $selectedAmiPeriodId = null;

    public ?string $selectedCategory = null;

    public ?string $selectedStatus = null;

    public string $search = '';

    public bool $isDetailOpen = false;

    public ?int $selectedFindingId = null;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return (bool) ($user?->isSuperAdmin() || $user?->isAdminLpm());
    }

    /**
     * @return array<int, string>
     */
    public function unitOptions(): array
    {
        return Unit::query()
            ->active()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public function periodOptions(): array
    {
        return AmiPeriod::query()
            ->orderByDesc('start_date')
            ->pluck('name', 'id')
            ->all();
    }

    /**
     * @return Paginator<int, AmiFinding>
     */
    public function findings(): Paginator
    {
        return $this->baseQuery()
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

    /**
     * @return array<string, int>
     */
    public function stats(): array
    {
        $query = $this->baseQuery();

        return [
            'total' => (clone $query)->count(),
            'open' => (clone $query)->where('status', AmiFindingStatus::Open->value)->count(),
            'waiting' => (clone $query)->where('status', AmiFindingStatus::WaitingVerification->value)->count(),
            'revision' => (clone $query)->where('status', AmiFindingStatus::NeedRevision->value)->count(),
            'closed' => (clone $query)->where('status', AmiFindingStatus::Closed->value)->count(),
            'overdue' => (clone $query)
                ->where('status', '!=', AmiFindingStatus::Closed->value)
                ->whereDate('due_date', '<', today())
                ->count(),
        ];
    }

    public function openDetail(int $findingId): void
    {
        $this->selectedFindingId = $findingId;
        $this->isDetailOpen = true;
    }

    public function closeDetail(): void
    {
        $this->selectedFindingId = null;
        $this->isDetailOpen = false;
    }

    public function selectedFinding(): ?AmiFinding
    {
        if ($this->selectedFindingId === null) {
            return null;
        }

        return $this->baseQuery()->findOrFail($this->selectedFindingId);
    }

    /**
     * @return array<string, string>
     */
    public function categoryOptions(): array
    {
        return collect(AmiFindingCategory::cases())
            ->mapWithKeys(fn (AmiFindingCategory $category): array => [$category->value => $category->getLabel()])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function statusOptions(): array
    {
        return collect(AmiFindingStatus::cases())
            ->mapWithKeys(fn (AmiFindingStatus $status): array => [$status->value => $status->getLabel()])
            ->all();
    }

    private function baseQuery(): Builder
    {
        return AmiFinding::query()
            ->with([
                'audit.auditeeUnit',
                'audit.amiPeriod',
                'standardIndicator.qualityStandard',
                'latestCorrectiveAction.picUser',
                'latestCorrectiveAction.evidences',
                'latestCorrectiveAction.reviews.reviewer',
            ])
            ->when($this->selectedUnitId !== null, fn (Builder $query): Builder => $query
                ->whereHas('audit', fn (Builder $auditQuery): Builder => $auditQuery->where('auditee_unit_id', $this->selectedUnitId)))
            ->when($this->selectedAmiPeriodId !== null, fn (Builder $query): Builder => $query
                ->whereHas('audit', fn (Builder $auditQuery): Builder => $auditQuery->where('ami_period_id', $this->selectedAmiPeriodId)))
            ->when(filled($this->selectedCategory), fn (Builder $query): Builder => $query->where('category', $this->selectedCategory))
            ->when(filled($this->selectedStatus), fn (Builder $query): Builder => $query->where('status', $this->selectedStatus));
    }
}
