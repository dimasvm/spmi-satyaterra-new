<?php

namespace Tests\Feature;

use App\Enums\IndicatorAssignmentStatus;
use App\Enums\QualityStandardStatus;
use App\Enums\SpmiPeriodStatus;
use App\Enums\StandardIndicatorType;
use App\Filament\Pages\SiklusSpmi;
use App\Models\IndicatorAchievement;
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
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SiklusSpmiPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_admin_lpm_can_view_spmi_cycle_map(): void
    {
        $user = User::factory()->create()->assignRole(Role::findOrCreate('admin_lpm', 'web'));
        $period = SpmiPeriod::query()->create([
            'name' => 'SPMI 2026',
            'academic_year' => '2026/2027',
            'semester' => 'ganjil',
            'start_date' => '2026-07-01',
            'end_date' => '2026-12-31',
            'status' => SpmiPeriodStatus::Active,
        ]);
        $unit = Unit::query()->create([
            'code' => 'LPM',
            'name' => 'Unit LPM',
            'is_active' => true,
        ]);
        $category = StandardCategory::query()->create([
            'code' => 'STD',
            'name' => 'Standar',
        ]);
        $standard = QualityStandard::query()->create([
            'standard_category_id' => $category->id,
            'spmi_period_id' => $period->id,
            'code' => 'STD-001',
            'name' => 'Standar Pendidikan',
            'status' => QualityStandardStatus::Active,
            'version' => 1,
        ]);
        $indicator = StandardIndicator::query()->create([
            'quality_standard_id' => $standard->id,
            'code' => 'IKU-001',
            'statement' => 'Indikator mutu pendidikan',
            'indicator_type' => StandardIndicatorType::Percentage,
            'target_operator' => '>=',
            'target_value' => 80,
            'target_unit' => '%',
        ]);
        $assignment = IndicatorUnitAssignment::query()->create([
            'standard_indicator_id' => $indicator->id,
            'unit_id' => $unit->id,
            'spmi_period_id' => $period->id,
            'due_date' => '2026-12-01',
            'status' => IndicatorAssignmentStatus::Assigned,
            'is_primary_pic' => true,
            'priority' => 'normal',
            'assigned_by' => $user->id,
            'assigned_at' => now(),
        ]);

        IndicatorAchievement::query()->create([
            'assignment_id' => $assignment->id,
            'submission_status' => 'submitted',
        ]);

        $this->actingAs($user);

        Livewire::test(SiklusSpmi::class)
            ->assertSee('Siklus SPMI')
            ->assertSee('Penetapan Standar')
            ->assertSee('Pelaksanaan Standar')
            ->assertSee('Evaluasi AMI')
            ->assertSee('Pengendalian Temuan')
            ->assertSee('Peningkatan Standar')
            ->assertSee('Total assignment')
            ->assertSee('Sudah submit');
    }
}
