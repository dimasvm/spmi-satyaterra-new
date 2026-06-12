<?php

namespace Tests\Feature;

use App\Enums\IndicatorAssignmentPriority;
use App\Enums\IndicatorAssignmentStatus;
use App\Enums\QualityStandardStatus;
use App\Enums\SpmiPeriodStatus;
use App\Enums\StandardIndicatorType;
use App\Filament\Resources\IndicatorUnitAssignments\Pages\AssignmentDetail;
use App\Filament\Resources\IndicatorUnitAssignments\Pages\AssignmentMatrix;
use App\Filament\Resources\IndicatorUnitAssignments\Pages\CreateIndicatorUnitAssignment;
use App\Filament\Resources\IndicatorUnitAssignments\Pages\ListIndicatorUnitAssignments;
use App\Filament\Resources\StandardIndicators\Pages\ListStandardIndicators;
use App\Models\IndicatorAchievement;
use App\Models\IndicatorAssignmentEvent;
use App\Models\IndicatorUnitAssignment;
use App\Models\QualityStandard;
use App\Models\SpmiPeriod;
use App\Models\StandardCategory;
use App\Models\StandardIndicator;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
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

    public function test_custom_assignment_dashboard_renders_stats(): void
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

        Livewire::test(ListIndicatorUnitAssignments::class)
            ->set('period', $period->id)
            ->assertSee('Penugasan Indikator')
            ->assertSee('IKU-001')
            ->assertSee('UPM');
    }

    public function test_custom_assignment_dashboard_can_create_mass_assignment_with_timeline(): void
    {
        $indicator = $this->createIndicator('IKU-001');
        $unit = $this->createUnit('UPM');
        $period = $this->createSpmiPeriod();

        Livewire::test(ListIndicatorUnitAssignments::class)
            ->set('period', $period->id)
            ->set('selectedIndicatorIds', [$indicator->id])
            ->set('selectedUnitIds', [$unit->id])
            ->set('primaryPicUnitId', $unit->id)
            ->set('dueDate', '2026-06-30')
            ->set('priority', 'high')
            ->set('notes', 'Prioritas audit.')
            ->call('assignSelectedIndicators')
            ->assertNotified();

        $this->assertDatabaseHas(IndicatorUnitAssignment::class, [
            'standard_indicator_id' => $indicator->id,
            'unit_id' => $unit->id,
            'spmi_period_id' => $period->id,
            'is_primary_pic' => true,
            'priority' => 'high',
            'notes' => 'Prioritas audit.',
        ]);
        $this->assertDatabaseHas(IndicatorAssignmentEvent::class, [
            'event' => 'assigned',
        ]);
    }

    public function test_assignment_matrix_renders_indicator_unit_distribution(): void
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
            'is_primary_pic' => true,
            'priority' => 'normal',
        ]);

        Livewire::test(AssignmentMatrix::class)
            ->set('period', $period->id)
            ->assertSee('Matriks Penugasan Indikator')
            ->assertSee('IKU-001')
            ->assertSee('UPM');
    }

    public function test_assignment_detail_can_record_reminder_event(): void
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
            'is_primary_pic' => true,
            'priority' => 'normal',
        ]);

        Livewire::test(AssignmentDetail::class, ['indicator' => $indicator])
            ->set('period', $period->id)
            ->call('sendReminder')
            ->assertNotified();

        $this->assertDatabaseHas(IndicatorAssignmentEvent::class, [
            'event' => 'reminder_sent',
        ]);
    }

    public function test_assignment_detail_can_edit_assignment(): void
    {
        $indicator = $this->createIndicator('IKU-001');
        $firstUnit = $this->createUnit('UPM');
        $secondUnit = $this->createUnit('LPM');
        $thirdUnit = $this->createUnit('PRODI');
        $period = $this->createSpmiPeriod();

        $firstAssignment = IndicatorUnitAssignment::query()->create([
            'standard_indicator_id' => $indicator->id,
            'unit_id' => $firstUnit->id,
            'spmi_period_id' => $period->id,
            'due_date' => '2026-06-30',
            'status' => IndicatorAssignmentStatus::Assigned,
            'is_primary_pic' => true,
            'priority' => 'normal',
        ]);

        $secondAssignment = IndicatorUnitAssignment::query()->create([
            'standard_indicator_id' => $indicator->id,
            'unit_id' => $secondUnit->id,
            'spmi_period_id' => $period->id,
            'due_date' => '2026-06-30',
            'status' => IndicatorAssignmentStatus::Assigned,
            'is_primary_pic' => false,
            'priority' => 'normal',
        ]);

        Livewire::test(AssignmentDetail::class, ['indicator' => $indicator])
            ->set('period', $period->id)
            ->call('openEditAssignmentModal', $secondAssignment->id)
            ->set('editUnitId', $thirdUnit->id)
            ->set('editPeriodId', $period->id)
            ->set('editDueDate', '2026-07-15')
            ->set('editStatus', IndicatorAssignmentStatus::InProgress->value)
            ->set('editIsPrimaryPic', true)
            ->set('editPriority', IndicatorAssignmentPriority::High->value)
            ->set('editNotes', 'Diubah menjadi PIC utama.')
            ->call('updateAssignment')
            ->assertNotified();

        $this->assertDatabaseHas(IndicatorUnitAssignment::class, [
            'id' => $secondAssignment->id,
            'unit_id' => $thirdUnit->id,
            'spmi_period_id' => $period->id,
            'due_date' => '2026-07-15 00:00:00',
            'status' => IndicatorAssignmentStatus::InProgress->value,
            'is_primary_pic' => true,
            'priority' => IndicatorAssignmentPriority::High->value,
            'notes' => 'Diubah menjadi PIC utama.',
        ]);

        $this->assertDatabaseHas(IndicatorUnitAssignment::class, [
            'id' => $firstAssignment->id,
            'is_primary_pic' => false,
        ]);
    }

    public function test_assignment_detail_quick_action_opens_edit_assignment_modal(): void
    {
        $indicator = $this->createIndicator('IKU-001');
        $unit = $this->createUnit('UPM');
        $period = $this->createSpmiPeriod();

        $assignment = IndicatorUnitAssignment::query()->create([
            'standard_indicator_id' => $indicator->id,
            'unit_id' => $unit->id,
            'spmi_period_id' => $period->id,
            'due_date' => '2026-06-30',
            'status' => IndicatorAssignmentStatus::Assigned,
            'is_primary_pic' => true,
            'priority' => IndicatorAssignmentPriority::Normal,
        ]);

        Livewire::test(AssignmentDetail::class, ['indicator' => $indicator])
            ->set('period', $period->id)
            ->call('openFirstAssignmentEditModal')
            ->assertSet('isEditAssignmentModalOpen', true)
            ->assertSet('editingAssignmentId', $assignment->id)
            ->assertSet('editUnitId', $unit->id)
            ->assertSee('Edit Penugasan');
    }

    public function test_assignment_detail_can_delete_assignment(): void
    {
        $indicator = $this->createIndicator('IKU-001');
        $unit = $this->createUnit('UPM');
        $period = $this->createSpmiPeriod();

        $assignment = IndicatorUnitAssignment::query()->create([
            'standard_indicator_id' => $indicator->id,
            'unit_id' => $unit->id,
            'spmi_period_id' => $period->id,
            'due_date' => '2026-06-30',
            'status' => IndicatorAssignmentStatus::Assigned,
            'is_primary_pic' => true,
            'priority' => 'normal',
        ]);

        $achievement = IndicatorAchievement::query()->create([
            'assignment_id' => $assignment->id,
        ]);

        Livewire::test(AssignmentDetail::class, ['indicator' => $indicator])
            ->set('period', $period->id)
            ->call('deleteAssignment', $assignment->id)
            ->assertNotified();

        $this->assertDatabaseMissing(IndicatorUnitAssignment::class, [
            'id' => $assignment->id,
        ]);
        $this->assertDatabaseMissing(IndicatorAchievement::class, [
            'id' => $achievement->id,
        ]);
        $this->assertDatabaseMissing(IndicatorAssignmentEvent::class, [
            'indicator_unit_assignment_id' => $assignment->id,
        ]);
    }

    public function test_standard_indicator_table_can_render_assigned_units(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('admin_lpm');

        $this->actingAs($user);

        $indicator = $this->createIndicator('IKU-001');
        $unit = $this->createUnit('UPM');
        $period = $this->createSpmiPeriod();

        IndicatorUnitAssignment::query()->create([
            'standard_indicator_id' => $indicator->id,
            'unit_id' => $unit->id,
            'spmi_period_id' => $period->id,
            'due_date' => '2026-06-30',
            'status' => IndicatorAssignmentStatus::Assigned,
            'priority' => IndicatorAssignmentPriority::Normal,
        ]);

        Livewire::test(ListStandardIndicators::class)
            ->assertOk()
            ->assertCanSeeTableRecords([$indicator])
            ->assertSee('UPM');
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
