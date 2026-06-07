<?php

namespace Tests\Feature;

use App\Enums\AchievementStatus;
use App\Enums\EvidenceFileType;
use App\Enums\IndicatorAssignmentStatus;
use App\Enums\QualityStandardStatus;
use App\Enums\SpmiPeriodStatus;
use App\Enums\StandardIndicatorType;
use App\Enums\SubmissionStatus;
use App\Filament\Resources\IndicatorAchievements\IndicatorAchievementResource;
use App\Filament\Resources\IndicatorAchievements\Pages\EditIndicatorAchievement;
use App\Filament\Resources\IndicatorAchievements\Pages\ListIndicatorAchievements;
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
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IndicatorAchievementResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_unit_user_sees_only_assigned_indicators_for_their_unit(): void
    {
        $firstUnit = $this->createUnit('PRD');
        $secondUnit = $this->createUnit('LPM');
        $user = $this->createUnitUser($firstUnit);
        $firstAssignment = $this->createAssignment($firstUnit, 'IKU-001');
        $secondAssignment = $this->createAssignment($secondUnit, 'IKU-002');

        IndicatorAchievement::query()->create([
            'assignment_id' => $secondAssignment->id,
            'submission_status' => SubmissionStatus::Draft,
        ]);

        $this->actingAs($user);

        Livewire::test(ListIndicatorAchievements::class)
            ->assertSee('IKU-001')
            ->assertDontSee('IKU-002');

        $this->assertDatabaseHas(IndicatorAchievement::class, [
            'assignment_id' => $firstAssignment->id,
            'submission_status' => SubmissionStatus::Draft->value,
            'submitted_by' => null,
        ]);
    }

    public function test_unit_user_can_save_draft_achievement(): void
    {
        $unit = $this->createUnit('PRD');
        $user = $this->createUnitUser($unit);
        $achievement = IndicatorAchievement::query()->create([
            'assignment_id' => $this->createAssignment($unit, 'IKU-001')->id,
            'submission_status' => SubmissionStatus::Draft,
        ]);

        $this->actingAs($user);

        Livewire::test(EditIndicatorAchievement::class, ['record' => $achievement->id])
            ->fillForm([
                'realization_value' => 87.5,
                'realization_text' => 'Target pembelajaran tercapai sesuai instrumen monitoring.',
                'achievement_status' => AchievementStatus::Achieved->value,
                'notes' => 'Dokumen pendukung menyusul.',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(IndicatorAchievement::class, [
            'id' => $achievement->id,
            'realization_value' => 87.5,
            'realization_text' => 'Target pembelajaran tercapai sesuai instrumen monitoring.',
            'achievement_status' => AchievementStatus::Achieved->value,
            'submission_status' => SubmissionStatus::Draft->value,
        ]);
    }

    public function test_unit_user_can_submit_achievement_with_evidence_link(): void
    {
        $unit = $this->createUnit('PRD');
        $user = $this->createUnitUser($unit);
        $achievement = IndicatorAchievement::query()->create([
            'assignment_id' => $this->createAssignment($unit, 'IKU-001')->id,
            'submission_status' => SubmissionStatus::Draft,
        ]);

        $this->actingAs($user);

        Livewire::test(EditIndicatorAchievement::class, ['record' => $achievement->id])
            ->fillForm([
                'realization_value' => 90,
                'realization_text' => 'Seluruh aktivitas mutu sudah dilaksanakan.',
                'achievement_status' => AchievementStatus::Achieved->value,
                'notes' => 'Siap direview.',
                'evidences' => [
                    [
                        'file_type' => EvidenceFileType::Link->value,
                        'external_url' => 'https://example.com/bukti-capaian',
                        'description' => 'Folder bukti capaian.',
                    ],
                ],
            ])
            ->call('submitAchievement')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(IndicatorAchievement::class, [
            'id' => $achievement->id,
            'submission_status' => SubmissionStatus::Submitted->value,
            'submitted_by' => $user->id,
        ]);

        $this->assertDatabaseHas('achievement_evidences', [
            'indicator_achievement_id' => $achievement->id,
            'file_type' => EvidenceFileType::Link->value,
            'external_url' => 'https://example.com/bukti-capaian',
            'uploaded_by' => $user->id,
        ]);
    }

    public function test_unit_user_cannot_edit_submitted_achievement(): void
    {
        $unit = $this->createUnit('PRD');
        $user = $this->createUnitUser($unit);
        $achievement = IndicatorAchievement::query()->create([
            'assignment_id' => $this->createAssignment($unit, 'IKU-001')->id,
            'submission_status' => SubmissionStatus::Submitted,
            'submitted_at' => now(),
            'submitted_by' => $user->id,
        ]);

        $this->actingAs($user);

        $this->get(IndicatorAchievementResource::getUrl('edit', ['record' => $achievement]))
            ->assertForbidden();
    }

    private function createUnitUser(Unit $unit): User
    {
        $permissions = [
            'indicator-achievements.view',
            'indicator-achievements.create',
            'indicator-achievements.update',
            'indicator-achievements.submit',
            'achievement-evidences.view',
            'achievement-evidences.create',
            'achievement-evidences.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $role = Role::findOrCreate('unit_pic', 'web');
        $role->syncPermissions($permissions);

        return User::factory()
            ->create([
                'unit_id' => $unit->id,
            ])
            ->assignRole($role);
    }

    private function createAssignment(Unit $unit, string $indicatorCode): IndicatorUnitAssignment
    {
        return IndicatorUnitAssignment::query()->create([
            'standard_indicator_id' => $this->createIndicator($indicatorCode)->id,
            'unit_id' => $unit->id,
            'spmi_period_id' => $this->createSpmiPeriod()->id,
            'due_date' => '2026-06-30',
            'status' => IndicatorAssignmentStatus::Assigned,
            'is_primary_pic' => true,
            'priority' => 'normal',
        ]);
    }

    private function createIndicator(string $code): StandardIndicator
    {
        $category = StandardCategory::query()->firstOrCreate(
            ['code' => 'STD'],
            ['name' => 'Standar', 'description' => null],
        );

        $qualityStandard = QualityStandard::query()->firstOrCreate(
            ['code' => "QS-{$code}", 'spmi_period_id' => null],
            [
                'standard_category_id' => $category->id,
                'name' => "Standar Mutu {$code}",
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
