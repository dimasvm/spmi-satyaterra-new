<?php

namespace Tests\Feature;

use App\Enums\IndicatorAssignmentStatus;
use App\Enums\QualityStandardStatus;
use App\Enums\SpmiPeriodStatus;
use App\Enums\StandardIndicatorType;
use App\Filament\Resources\IndicatorUnitAssignments\Pages\CreateIndicatorUnitAssignment;
use App\Filament\Resources\StandardIndicators\Pages\ListStandardIndicators;
use App\Models\IndicatorUnitAssignment;
use App\Models\QualityStandard;
use App\Models\SpmiPeriod;
use App\Models\StandardCategory;
use App\Models\StandardIndicator;
use App\Models\Unit;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IndicatorUnitAssignmentResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $this->actingAs(User::factory()->create());
    }

    public function test_it_can_create_indicator_unit_assignment_from_resource(): void
    {
        $indicator = $this->createIndicator('IKU-001');
        $unit = $this->createUnit('UPM');
        $period = $this->createSpmiPeriod();

        Livewire::test(CreateIndicatorUnitAssignment::class)
            ->fillForm([
                'standard_indicator_id' => $indicator->id,
                'unit_id' => $unit->id,
                'spmi_period_id' => $period->id,
                'due_date' => '2026-06-30',
                'status' => IndicatorAssignmentStatus::Assigned->value,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(IndicatorUnitAssignment::class, [
            'standard_indicator_id' => $indicator->id,
            'unit_id' => $unit->id,
            'spmi_period_id' => $period->id,
            'status' => IndicatorAssignmentStatus::Assigned->value,
        ]);
    }

    public function test_it_validates_duplicate_indicator_unit_period_assignment(): void
    {
        $indicator = $this->createIndicator('IKU-001');
        $unit = $this->createUnit('UPM');
        $period = $this->createSpmiPeriod();

        IndicatorUnitAssignment::query()->create([
            'standard_indicator_id' => $indicator->id,
            'unit_id' => $unit->id,
            'spmi_period_id' => $period->id,
            'due_date' => '2026-06-30',
            'status' => IndicatorAssignmentStatus::Assigned,
        ]);

        Livewire::test(CreateIndicatorUnitAssignment::class)
            ->fillForm([
                'standard_indicator_id' => $indicator->id,
                'unit_id' => $unit->id,
                'spmi_period_id' => $period->id,
                'due_date' => '2026-07-31',
                'status' => IndicatorAssignmentStatus::Assigned->value,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'spmi_period_id' => 'unique',
            ]);
    }

    public function test_it_can_assign_single_indicator_to_units_from_indicator_table(): void
    {
        $indicator = $this->createIndicator('IKU-001');
        $unit = $this->createUnit('UPM');
        $period = $this->createSpmiPeriod();

        Livewire::test(ListStandardIndicators::class)
            ->callAction(TestAction::make('assignToUnits')->table($indicator), [
                'unit_ids' => [$unit->id],
                'spmi_period_id' => $period->id,
                'due_date' => '2026-06-30',
                'status' => IndicatorAssignmentStatus::Assigned->value,
            ])
            ->assertNotified()
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas(IndicatorUnitAssignment::class, [
            'standard_indicator_id' => $indicator->id,
            'unit_id' => $unit->id,
            'spmi_period_id' => $period->id,
        ]);
    }

    public function test_it_can_bulk_assign_indicators_to_units_without_duplicates(): void
    {
        $firstIndicator = $this->createIndicator('IKU-001');
        $secondIndicator = $this->createIndicator('IKU-002');
        $firstUnit = $this->createUnit('UPM');
        $secondUnit = $this->createUnit('LPM');
        $period = $this->createSpmiPeriod();

        IndicatorUnitAssignment::query()->create([
            'standard_indicator_id' => $firstIndicator->id,
            'unit_id' => $firstUnit->id,
            'spmi_period_id' => $period->id,
            'due_date' => '2026-06-30',
            'status' => IndicatorAssignmentStatus::Assigned,
        ]);

        Livewire::test(ListStandardIndicators::class)
            ->selectTableRecords([$firstIndicator->id, $secondIndicator->id])
            ->callAction(TestAction::make('assignToUnits')->table()->bulk(), [
                'unit_ids' => [$firstUnit->id, $secondUnit->id],
                'spmi_period_id' => $period->id,
                'due_date' => '2026-07-31',
                'status' => IndicatorAssignmentStatus::Assigned->value,
            ])
            ->assertNotified()
            ->assertHasNoActionErrors();

        $this->assertDatabaseCount((new IndicatorUnitAssignment)->getTable(), 4);
        $this->assertDatabaseHas(IndicatorUnitAssignment::class, [
            'standard_indicator_id' => $secondIndicator->id,
            'unit_id' => $secondUnit->id,
            'spmi_period_id' => $period->id,
        ]);
    }

    private function createIndicator(string $code): StandardIndicator
    {
        $category = StandardCategory::query()->firstOrCreate(
            ['code' => 'STD'],
            ['name' => 'Standar', 'description' => null],
        );

        $qualityStandard = QualityStandard::query()->firstOrCreate(
            ['code' => 'QS-001', 'spmi_period_id' => null],
            [
                'standard_category_id' => $category->id,
                'name' => 'Standar Mutu 001',
                'status' => QualityStandardStatus::Draft,
                'version' => 1,
            ],
        );

        return StandardIndicator::query()->create([
            'quality_standard_id' => $qualityStandard->id,
            'code' => $code,
            'statement' => "Pernyataan {$code}",
            'indicator_type' => StandardIndicatorType::Percentage,
            'target_value' => 80,
            'target_operator' => '>=',
            'target_unit' => '%',
            'weight' => 1,
            'evidence_required' => true,
            'evidence_description' => 'Dokumen pendukung.',
        ]);
    }

    private function createUnit(string $code): Unit
    {
        return Unit::query()->create([
            'code' => $code,
            'name' => "Unit {$code}",
            'type' => null,
            'is_active' => true,
        ]);
    }

    private function createSpmiPeriod(): SpmiPeriod
    {
        return SpmiPeriod::query()->create([
            'name' => 'SPMI 2026',
            'academic_year' => '2025/2026',
            'semester' => null,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => SpmiPeriodStatus::Active,
        ]);
    }
}
