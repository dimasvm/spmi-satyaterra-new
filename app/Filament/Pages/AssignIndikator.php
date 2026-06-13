<?php

namespace App\Filament\Pages;

use App\Actions\AssignIndicatorsToUnits;
use App\Enums\IndicatorAssignmentPriority;
use App\Enums\IndicatorAssignmentStatus;
use App\Enums\UnitType;
use App\Filament\Resources\IndicatorUnitAssignments\IndicatorUnitAssignmentResource;
use App\Models\IndicatorUnitAssignment;
use App\Models\QualityStandard;
use App\Models\SpmiPeriod;
use App\Models\StandardIndicator;
use App\Models\Unit;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

class AssignIndikator extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserPlus;

    protected static string|UnitEnum|null $navigationGroup = 'Penetapan';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Assign Indikator';

    protected static ?string $title = 'Assign Indikator';

    protected string $view = 'filament.pages.assign-indikator';

    public int $currentStep = 1;

    public ?int $spmiPeriodId = null;

    public ?int $qualityStandardId = null;

    /** @var array<int, int|string> */
    public array $standardIndicatorIds = [];

    /** @var array<int, int|string> */
    public array $unitIds = [];

    public ?string $unitType = null;

    public ?string $dueDate = null;

    public string $status = IndicatorAssignmentStatus::Assigned->value;

    public string $priority = IndicatorAssignmentPriority::Normal->value;

    public ?string $notes = null;

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->can('create', IndicatorUnitAssignment::class);
    }

    public function mount(): void
    {
        $this->spmiPeriodId = SpmiPeriod::active()->value('id')
            ?? SpmiPeriod::query()->latest('start_date')->value('id');
    }

    /**
     * @return array<int, array{label: string, description: string}>
     */
    public function steps(): array
    {
        return [
            1 => ['label' => 'Periode', 'description' => 'Pilih siklus SPMI'],
            2 => ['label' => 'Standar', 'description' => 'Pilih standar mutu'],
            3 => ['label' => 'Indikator', 'description' => 'Tentukan target'],
            4 => ['label' => 'Unit', 'description' => 'Pilih pelaksana'],
            5 => ['label' => 'Deadline', 'description' => 'Atur batas waktu'],
            6 => ['label' => 'Review', 'description' => 'Cek ringkasan'],
        ];
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
     * @return array<int, string>
     */
    public function standardOptions(): array
    {
        return QualityStandard::query()
            ->when($this->spmiPeriodId !== null, fn ($query) => $query->where('spmi_period_id', $this->spmiPeriodId))
            ->orderBy('code')
            ->pluck('name', 'id')
            ->all();
    }

    public function selectedPeriod(): ?SpmiPeriod
    {
        return $this->spmiPeriodId !== null
            ? SpmiPeriod::query()->find($this->spmiPeriodId)
            : null;
    }

    public function selectedStandard(): ?QualityStandard
    {
        return $this->qualityStandardId !== null
            ? QualityStandard::query()->with('category')->find($this->qualityStandardId)
            : null;
    }

    /**
     * @return Collection<int, StandardIndicator>
     */
    public function availableIndicators(): Collection
    {
        return StandardIndicator::query()
            ->where('quality_standard_id', $this->qualityStandardId)
            ->orderBy('code')
            ->get();
    }

    /**
     * @return Collection<int, StandardIndicator>
     */
    public function selectedIndicators(): Collection
    {
        return StandardIndicator::query()
            ->whereIn('id', $this->standardIndicatorIds)
            ->orderBy('code')
            ->get();
    }

    /**
     * @return array<string, string>
     */
    public function unitTypeOptions(): array
    {
        return collect(UnitType::cases())
            ->mapWithKeys(fn (UnitType $type): array => [$type->value => (string) $type->getLabel()])
            ->all();
    }

    /**
     * @return Collection<int, Unit>
     */
    public function availableUnits(): Collection
    {
        return Unit::query()
            ->active()
            ->when($this->unitType !== null && $this->unitType !== '', fn ($query) => $query->where('type', $this->unitType))
            ->orderBy('type')
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, Unit>
     */
    public function selectedUnits(): Collection
    {
        return Unit::query()
            ->whereIn('id', $this->unitIds)
            ->orderBy('name')
            ->get();
    }

    /**
     * @return array<string, int|string|null>
     */
    public function reviewSummary(): array
    {
        $indicatorCount = count($this->standardIndicatorIds);
        $unitCount = count($this->unitIds);
        $existingCount = 0;

        if ($this->spmiPeriodId !== null && $indicatorCount > 0 && $unitCount > 0) {
            $existingCount = IndicatorUnitAssignment::query()
                ->where('spmi_period_id', $this->spmiPeriodId)
                ->whereIn('standard_indicator_id', $this->standardIndicatorIds)
                ->whereIn('unit_id', $this->unitIds)
                ->count();
        }

        return [
            'period' => $this->selectedPeriod()?->name,
            'standard' => $this->selectedStandard()?->name,
            'indicator_count' => $indicatorCount,
            'unit_count' => $unitCount,
            'total_assignments' => $indicatorCount * $unitCount,
            'existing_assignments' => $existingCount,
            'new_assignments' => max(($indicatorCount * $unitCount) - $existingCount, 0),
            'due_date' => $this->dueDate,
        ];
    }

    public function updatedSpmiPeriodId(): void
    {
        $this->qualityStandardId = null;
        $this->standardIndicatorIds = [];
    }

    public function updatedQualityStandardId(): void
    {
        $this->standardIndicatorIds = [];
    }

    public function selectAllIndicators(): void
    {
        $this->standardIndicatorIds = $this->availableIndicators()
            ->pluck('id')
            ->map(fn (int $id): string => (string) $id)
            ->all();
    }

    public function selectAllUnits(): void
    {
        $this->unitIds = $this->availableUnits()
            ->pluck('id')
            ->map(fn (int $id): string => (string) $id)
            ->all();
    }

    public function selectAllStudyPrograms(): void
    {
        $this->unitType = UnitType::StudyProgram->value;
        $this->unitIds = $this->availableUnits()
            ->pluck('id')
            ->map(fn (int $id): string => (string) $id)
            ->all();
    }

    public function nextStep(): void
    {
        $this->validateCurrentStep();

        $this->currentStep = min($this->currentStep + 1, count($this->steps()));
    }

    public function previousStep(): void
    {
        $this->currentStep = max($this->currentStep - 1, 1);
    }

    public function goToStep(int $step): void
    {
        if ($step < $this->currentStep) {
            $this->currentStep = max($step, 1);
        }
    }

    public function submit(AssignIndicatorsToUnits $assignIndicatorsToUnits): mixed
    {
        $this->validate($this->rules());

        $result = $assignIndicatorsToUnits->handle(
            indicators: $this->selectedIndicators(),
            unitIds: $this->unitIds,
            spmiPeriodId: $this->spmiPeriodId,
            dueDate: $this->dueDate,
            status: $this->status,
            priority: $this->priority,
            notes: $this->notes,
            assignedBy: auth()->id(),
        );

        $body = "{$result['created']} penugasan baru dibuat.";

        if (($result['updated'] ?? 0) > 0) {
            $body .= " {$result['updated']} penugasan diperbarui.";
        }

        Notification::make()
            ->title('Assign indikator berhasil')
            ->body($body)
            ->success()
            ->send();

        return redirect()->to(IndicatorUnitAssignmentResource::getUrl('index'));
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'spmiPeriodId' => ['required', 'integer', 'exists:spmi_periods,id'],
            'qualityStandardId' => ['required', 'integer', 'exists:quality_standards,id'],
            'standardIndicatorIds' => ['required', 'array', 'min:1'],
            'standardIndicatorIds.*' => ['integer', 'exists:standard_indicators,id'],
            'unitIds' => ['required', 'array', 'min:1'],
            'unitIds.*' => ['integer', 'exists:units,id'],
            'dueDate' => ['nullable', 'date'],
            'status' => ['required', 'string'],
            'priority' => ['required', 'string'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    private function validateCurrentStep(): void
    {
        $stepRules = match ($this->currentStep) {
            1 => ['spmiPeriodId' => $this->rules()['spmiPeriodId']],
            2 => ['qualityStandardId' => $this->rules()['qualityStandardId']],
            3 => [
                'standardIndicatorIds' => $this->rules()['standardIndicatorIds'],
                'standardIndicatorIds.*' => $this->rules()['standardIndicatorIds.*'],
            ],
            4 => [
                'unitIds' => $this->rules()['unitIds'],
                'unitIds.*' => $this->rules()['unitIds.*'],
            ],
            5 => [
                'dueDate' => $this->rules()['dueDate'],
                'status' => $this->rules()['status'],
                'priority' => $this->rules()['priority'],
                'notes' => $this->rules()['notes'],
            ],
            default => [],
        };

        if ($stepRules !== []) {
            $this->validate($stepRules);
        }
    }
}
