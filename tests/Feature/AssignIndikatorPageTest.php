<?php

namespace Tests\Feature;

use App\Enums\IndicatorAssignmentPriority;
use App\Enums\IndicatorAssignmentStatus;
use App\Enums\QualityStandardStatus;
use App\Enums\SpmiPeriodStatus;
use App\Enums\StandardIndicatorType;
use App\Enums\UnitType;
use App\Filament\Pages\AssignIndikator;
use App\Filament\Resources\IndicatorUnitAssignments\IndicatorUnitAssignmentResource;
use App\Models\IndicatorUnitAssignment;
use App\Models\QualityStandard;
use App\Models\SpmiPeriod;
use App\Models\StandardCategory;
use App\Models\StandardIndicator;
use App\Models\Unit;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AssignIndikatorPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_admin_lpm_can_render_assignment_wizard(): void
    {
        $this->actingAs($this->createAdminLpm());

        Livewire::test(AssignIndikator::class)
            ->assertSee('Assign Indikator')
            ->assertSee('Periode')
            ->assertSee('Standar')
            ->assertSee('Indikator')
            ->assertSee('Unit')
            ->assertSee('Deadline')
            ->assertSee('Review');
    }

    public function test_admin_lpm_can_create_bulk_assignments_from_wizard(): void
    {
        $admin = $this->createAdminLpm();
        $period = $this->createSpmiPeriod();
        $standard = $this->createQualityStandard($period);
        $firstIndicator = $this->createIndicator($standard, 'IKU-001');
        $secondIndicator = $this->createIndicator($standard, 'IKU-002');
        $firstUnit = $this->createUnit('PSI', UnitType::StudyProgram);
        $secondUnit = $this->createUnit('FEB', UnitType::Faculty);

        $this->actingAs($admin);

        Livewire::test(AssignIndikator::class)
            ->set('spmiPeriodId', $period->id)
            ->set('qualityStandardId', $standard->id)
            ->set('standardIndicatorIds', [$firstIndicator->id, $secondIndicator->id])
            ->set('unitIds', [$firstUnit->id, $secondUnit->id])
            ->set('dueDate', '2026-12-15')
            ->call('submit')
            ->assertNotified()
            ->assertRedirect(IndicatorUnitAssignmentResource::getUrl('index'));

        $this->assertDatabaseCount(IndicatorUnitAssignment::class, 4);
        $this->assertDatabaseHas(IndicatorUnitAssignment::class, [
            'standard_indicator_id' => $firstIndicator->id,
            'unit_id' => $firstUnit->id,
            'spmi_period_id' => $period->id,
            'due_date' => '2026-12-15 00:00:00',
            'status' => IndicatorAssignmentStatus::Assigned->value,
            'priority' => IndicatorAssignmentPriority::Normal->value,
            'assigned_by' => $admin->id,
        ]);
    }

    public function test_wizard_updates_existing_assignment_without_duplicate(): void
    {
        $admin = $this->createAdminLpm();
        $period = $this->createSpmiPeriod();
        $standard = $this->createQualityStandard($period);
        $indicator = $this->createIndicator($standard, 'IKU-001');
        $unit = $this->createUnit('PSI', UnitType::StudyProgram);

        IndicatorUnitAssignment::query()->create([
            'standard_indicator_id' => $indicator->id,
            'unit_id' => $unit->id,
            'spmi_period_id' => $period->id,
            'due_date' => '2026-07-01',
            'status' => IndicatorAssignmentStatus::Assigned,
            'priority' => IndicatorAssignmentPriority::Normal,
            'assigned_by' => $admin->id,
            'assigned_at' => now()->subDay(),
        ]);

        $this->actingAs($admin);

        Livewire::test(AssignIndikator::class)
            ->set('spmiPeriodId', $period->id)
            ->set('qualityStandardId', $standard->id)
            ->set('standardIndicatorIds', [$indicator->id])
            ->set('unitIds', [$unit->id])
            ->set('dueDate', '2026-12-31')
            ->set('priority', IndicatorAssignmentPriority::High->value)
            ->set('notes', 'Update deadline dan prioritas.')
            ->call('submit')
            ->assertNotified()
            ->assertRedirect(IndicatorUnitAssignmentResource::getUrl('index'));

        $this->assertDatabaseCount(IndicatorUnitAssignment::class, 1);
        $this->assertDatabaseHas(IndicatorUnitAssignment::class, [
            'standard_indicator_id' => $indicator->id,
            'unit_id' => $unit->id,
            'spmi_period_id' => $period->id,
            'due_date' => '2026-12-31 00:00:00',
            'priority' => IndicatorAssignmentPriority::High->value,
            'notes' => 'Update deadline dan prioritas.',
        ]);
    }

    public function test_wizard_can_select_all_study_program_units(): void
    {
        $admin = $this->createAdminLpm();
        $this->createSpmiPeriod();
        $studyProgram = $this->createUnit('PSI', UnitType::StudyProgram);
        $this->createUnit('FEB', UnitType::Faculty);

        $this->actingAs($admin);

        Livewire::test(AssignIndikator::class)
            ->call('selectAllStudyPrograms')
            ->assertSet('unitType', UnitType::StudyProgram->value)
            ->assertSet('unitIds', [(string) $studyProgram->id]);
    }

    private function createAdminLpm(): User
    {
        Permission::findOrCreate('indicator-assignments.create', 'web');

        $role = Role::findOrCreate('admin_lpm', 'web');
        $role->syncPermissions(['indicator-assignments.create']);

        return User::factory()->create()->assignRole($role);
    }

    private function createSpmiPeriod(): SpmiPeriod
    {
        return SpmiPeriod::query()->create([
            'name' => 'SPMI 2026',
            'academic_year' => '2026/2027',
            'semester' => null,
            'start_date' => '2026-07-01',
            'end_date' => '2026-12-31',
            'status' => SpmiPeriodStatus::Active,
        ]);
    }

    private function createQualityStandard(SpmiPeriod $period): QualityStandard
    {
        $category = StandardCategory::query()->create([
            'code' => 'STD',
            'name' => 'Standar',
        ]);

        return QualityStandard::query()->create([
            'standard_category_id' => $category->id,
            'spmi_period_id' => $period->id,
            'code' => 'STD-001',
            'name' => 'Standar Pendidikan',
            'description' => 'Standar proses pendidikan.',
            'status' => QualityStandardStatus::Active,
            'version' => 1,
        ]);
    }

    private function createIndicator(QualityStandard $standard, string $code): StandardIndicator
    {
        return StandardIndicator::query()->create([
            'quality_standard_id' => $standard->id,
            'code' => $code,
            'statement' => "Indikator {$code}",
            'indicator_type' => StandardIndicatorType::Percentage,
            'target_operator' => '>=',
            'target_value' => 80,
            'target_unit' => '%',
            'weight' => 1,
            'evidence_required' => true,
        ]);
    }

    private function createUnit(string $code, UnitType $type): Unit
    {
        return Unit::query()->create([
            'code' => $code,
            'name' => "Unit {$code}",
            'type' => $type,
            'is_active' => true,
        ]);
    }
}
