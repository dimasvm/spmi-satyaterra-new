<?php

namespace Tests\Feature;

use App\Enums\AchievementReviewStatus;
use App\Enums\IndicatorAssignmentPriority;
use App\Enums\IndicatorAssignmentStatus;
use App\Enums\QualityStandardStatus;
use App\Enums\SpmiPeriodStatus;
use App\Enums\StandardIndicatorType;
use App\Enums\SubmissionStatus;
use App\Filament\Pages\CapaianIndikatorSaya;
use App\Filament\Pages\InboxValidasiCapaian;
use App\Filament\Pages\SpmiWorkflowSetting;
use App\Models\AchievementReview;
use App\Models\IndicatorAchievement;
use App\Models\IndicatorUnitAssignment;
use App\Models\QualityStandard;
use App\Models\SpmiPeriod;
use App\Models\StandardCategory;
use App\Models\StandardIndicator;
use App\Models\SystemSetting;
use App\Models\Unit;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SpmiValidationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_only_admin_and_super_admin_can_access_workflow_settings_page(): void
    {
        $admin = $this->createAdminLpm();
        $unitPic = $this->createUnitUser($this->createUnit('PSI'));

        $this->actingAs($admin);
        Livewire::test(SpmiWorkflowSetting::class)
            ->assertOk();

        $this->actingAs($unitPic);
        Livewire::test(SpmiWorkflowSetting::class)
            ->assertStatus(403);
    }

    public function test_saving_settings_toggles_validation_required(): void
    {
        $admin = $this->createAdminLpm();
        $this->actingAs($admin);

        SystemSetting::set('achievement_validation_required', true);
        $this->assertTrue(SystemSetting::get('achievement_validation_required', true));

        Livewire::test(SpmiWorkflowSetting::class)
            ->assertFormSet(['achievement_validation_required' => true])
            ->set('data.achievement_validation_required', false)
            ->call('save')
            ->assertNotified();

        $this->assertFalse(SystemSetting::get('achievement_validation_required', true));
    }

    public function test_workflow_with_validation_enabled(): void
    {
        SystemSetting::set('achievement_validation_required', true);

        $period = $this->createSpmiPeriod();
        $unit = $this->createUnit('PSI');
        $assignment = $this->createAssignment($period, $unit, 'IKU-001');
        $user = $this->createUnitUser($unit);

        $this->actingAs($user);

        Livewire::test(CapaianIndikatorSaya::class)
            ->call('openAchievementForm', $assignment->id)
            ->set('realizationValue', '85')
            ->set('externalUrl', 'https://example.com/bukti')
            ->call('submitAchievement')
            ->assertNotified();

        $achievement = IndicatorAchievement::query()->where('assignment_id', $assignment->id)->firstOrFail();

        $this->assertSame(SubmissionStatus::Submitted, $achievement->submission_status);
        $this->assertDatabaseHas(IndicatorUnitAssignment::class, [
            'id' => $assignment->id,
            'status' => IndicatorAssignmentStatus::Submitted->value,
        ]);
        $this->assertDatabaseHas(AchievementReview::class, [
            'indicator_achievement_id' => $achievement->id,
            'status' => AchievementReviewStatus::Pending->value,
        ]);
    }

    public function test_workflow_with_validation_disabled(): void
    {
        SystemSetting::set('achievement_validation_required', false);

        $period = $this->createSpmiPeriod();
        $unit = $this->createUnit('PSI');
        $assignment = $this->createAssignment($period, $unit, 'IKU-001');
        $user = $this->createUnitUser($unit);

        $this->actingAs($user);

        Livewire::test(CapaianIndikatorSaya::class)
            ->call('openAchievementForm', $assignment->id)
            ->set('realizationValue', '85')
            ->set('externalUrl', 'https://example.com/bukti')
            ->call('submitAchievement')
            ->assertNotified();

        $achievement = IndicatorAchievement::query()->where('assignment_id', $assignment->id)->firstOrFail();

        $this->assertSame(SubmissionStatus::Validated, $achievement->submission_status);
        $this->assertDatabaseHas(IndicatorUnitAssignment::class, [
            'id' => $assignment->id,
            'status' => IndicatorAssignmentStatus::Validated->value,
        ]);
        $this->assertDatabaseMissing(AchievementReview::class, [
            'indicator_achievement_id' => $achievement->id,
        ]);
    }

    public function test_inbox_defaults_to_validated_when_validation_disabled(): void
    {
        SystemSetting::set('achievement_validation_required', false);
        $admin = $this->createAdminLpm();

        $this->actingAs($admin);

        Livewire::test(InboxValidasiCapaian::class)
            ->assertSet('activeTab', 'validated');
    }

    private function createAdminLpm(): User
    {
        foreach ([
            'achievement-reviews.view',
            'indicator-achievements.review',
        ] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $role = Role::findOrCreate('admin_lpm', 'web');
        $role->syncPermissions([
            'achievement-reviews.view',
            'indicator-achievements.review',
        ]);

        return User::factory()->create()->assignRole($role);
    }

    private function createUnitUser(Unit $unit): User
    {
        foreach ([
            'indicator-achievements.view',
            'indicator-achievements.create',
            'indicator-achievements.update',
            'achievement-evidences.create',
        ] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $role = Role::findOrCreate('unit_pic', 'web');
        $role->syncPermissions([
            'indicator-achievements.view',
            'indicator-achievements.create',
            'indicator-achievements.update',
            'achievement-evidences.create',
        ]);

        return User::factory()
            ->create(['unit_id' => $unit->id])
            ->assignRole($role);
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

    private function createUnit(string $code): Unit
    {
        return Unit::query()->create([
            'code' => $code,
            'name' => "Unit {$code}",
            'type' => null,
            'is_active' => true,
        ]);
    }

    private function createAssignment(SpmiPeriod $period, Unit $unit, string $indicatorCode): IndicatorUnitAssignment
    {
        return IndicatorUnitAssignment::query()->create([
            'standard_indicator_id' => $this->createIndicator($period, $indicatorCode)->id,
            'unit_id' => $unit->id,
            'spmi_period_id' => $period->id,
            'due_date' => '2026-12-31',
            'status' => IndicatorAssignmentStatus::Assigned,
            'is_primary_pic' => true,
            'priority' => IndicatorAssignmentPriority::Normal,
        ]);
    }

    private function createIndicator(SpmiPeriod $period, string $code): StandardIndicator
    {
        $category = StandardCategory::query()->firstOrCreate(
            ['code' => 'STD'],
            ['name' => 'Standar', 'description' => null],
        );

        $qualityStandard = QualityStandard::query()->create([
            'standard_category_id' => $category->id,
            'spmi_period_id' => $period->id,
            'code' => "QS-{$code}",
            'name' => "Standar Mutu {$code}",
            'status' => QualityStandardStatus::Active,
            'version' => 1,
        ]);

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
        ]);
    }
}
