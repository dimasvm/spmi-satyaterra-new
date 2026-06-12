<?php

namespace Tests\Feature;

use App\Enums\AchievementReviewStatus;
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
use App\Filament\Resources\IndicatorAchievements\Pages\ViewIndicatorAchievement;
use App\Filament\Widgets\IndicatorUnitAssignmentTable;
use App\Models\AchievementReview;
use App\Models\IndicatorAchievement;
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

    public function test_opening_achievement_list_does_not_create_drafts_for_all_unit_assignments(): void
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

        Livewire::test(ListIndicatorAchievements::class);

        $this->assertDatabaseMissing(IndicatorAchievement::class, [
            'assignment_id' => $firstAssignment->id,
            'submission_status' => SubmissionStatus::Draft->value,
        ]);

        $this->assertDatabaseHas(IndicatorAchievement::class, [
            'assignment_id' => $secondAssignment->id,
            'submission_status' => SubmissionStatus::Draft->value,
        ]);
    }

    public function test_unit_user_can_filter_achievement_list_by_achievement_status_tabs(): void
    {
        $unit = $this->createUnit('PRD');
        $user = $this->createUnitUser($unit);
        $achievedAssignment = $this->createAssignment($unit, 'IKU-001');
        $notAchievedAssignment = $this->createAssignment($unit, 'IKU-002');

        IndicatorAchievement::query()->create([
            'assignment_id' => $achievedAssignment->id,
            'achievement_status' => AchievementStatus::Achieved,
            'submission_status' => SubmissionStatus::Submitted,
        ]);

        IndicatorAchievement::query()->create([
            'assignment_id' => $notAchievedAssignment->id,
            'achievement_status' => AchievementStatus::NotAchieved,
            'submission_status' => SubmissionStatus::Submitted,
        ]);

        $this->actingAs($user);

        Livewire::test(ListIndicatorAchievements::class)
            ->assertSee('Tercapai')
            ->assertSee('Tidak Tercapai')
            ->set('activeTab', AchievementStatus::Achieved->value)
            ->assertSee('Pernyataan IKU-001')
            ->assertDontSee('Pernyataan IKU-002');
    }

    public function test_unit_user_can_start_one_assignment_from_widget_without_creating_other_drafts(): void
    {
        $unit = $this->createUnit('PRD');
        $user = $this->createUnitUser($unit);
        $firstAssignment = $this->createAssignment($unit, 'IKU-001');
        $secondAssignment = $this->createAssignment($unit, 'IKU-002');

        $this->actingAs($user);

        Livewire::test(IndicatorUnitAssignmentTable::class)
            ->assertSee('IKU-001')
            ->assertSee('IKU-002')
            ->callAction(TestAction::make('isi_capaian')->table($firstAssignment));

        $this->assertDatabaseHas(IndicatorAchievement::class, [
            'assignment_id' => $firstAssignment->id,
            'submission_status' => SubmissionStatus::Draft->value,
        ]);

        $this->assertDatabaseMissing(IndicatorAchievement::class, [
            'assignment_id' => $secondAssignment->id,
            'submission_status' => SubmissionStatus::Draft->value,
        ]);

        $this->assertDatabaseHas(IndicatorUnitAssignment::class, [
            'id' => $firstAssignment->id,
            'status' => IndicatorAssignmentStatus::InProgress->value,
        ]);

        $this->assertDatabaseHas(IndicatorUnitAssignment::class, [
            'id' => $secondAssignment->id,
            'status' => IndicatorAssignmentStatus::Assigned->value,
        ]);
    }

    public function test_unit_user_can_save_draft_achievement(): void
    {
        $unit = $this->createUnit('PRD');
        $user = $this->createUnitUser($unit);
        $assignment = $this->createAssignment($unit, 'IKU-001');
        $achievement = IndicatorAchievement::query()->create([
            'assignment_id' => $assignment->id,
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

        $this->assertDatabaseHas(IndicatorUnitAssignment::class, [
            'id' => $assignment->id,
            'status' => IndicatorAssignmentStatus::InProgress->value,
        ]);
    }

    public function test_unit_user_can_submit_achievement_with_evidence_link(): void
    {
        $unit = $this->createUnit('PRD');
        $user = $this->createUnitUser($unit);
        $assignment = $this->createAssignment($unit, 'IKU-001');
        $achievement = IndicatorAchievement::query()->create([
            'assignment_id' => $assignment->id,
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

        $this->assertDatabaseHas(IndicatorUnitAssignment::class, [
            'id' => $assignment->id,
            'status' => IndicatorAssignmentStatus::Submitted->value,
        ]);

        $this->assertDatabaseHas(AchievementReview::class, [
            'indicator_achievement_id' => $achievement->id,
            'reviewer_id' => null,
            'status' => AchievementReviewStatus::Pending->value,
            'notes' => null,
            'reviewed_at' => null,
        ]);
    }

    public function test_unit_user_resubmitting_returned_achievement_keeps_review_history_and_creates_new_pending_review(): void
    {
        $unit = $this->createUnit('PRD');
        $user = $this->createUnitUser($unit);
        $reviewer = User::factory()->create();
        $assignment = $this->createAssignment($unit, 'IKU-010');
        $achievement = IndicatorAchievement::query()->create([
            'assignment_id' => $assignment->id,
            'realization_value' => 70,
            'realization_text' => 'Dokumen perlu dilengkapi.',
            'achievement_status' => AchievementStatus::PartiallyAchieved,
            'submission_status' => SubmissionStatus::Returned,
            'submitted_at' => now()->subDay(),
            'submitted_by' => $user->id,
        ]);

        AchievementReview::query()->create([
            'indicator_achievement_id' => $achievement->id,
            'reviewer_id' => $reviewer->id,
            'status' => AchievementReviewStatus::Returned,
            'notes' => 'Mohon lengkapi bukti.',
            'reviewed_at' => now()->subHour(),
        ]);

        $this->actingAs($user);

        Livewire::test(EditIndicatorAchievement::class, ['record' => $achievement->id])
            ->fillForm([
                'realization_value' => 90,
                'realization_text' => 'Bukti sudah dilengkapi.',
                'achievement_status' => AchievementStatus::Achieved->value,
                'notes' => 'Siap direview ulang.',
            ])
            ->call('submitAchievement')
            ->assertHasNoFormErrors();

        $this->assertDatabaseCount((new AchievementReview)->getTable(), 2);

        $this->assertDatabaseHas(AchievementReview::class, [
            'indicator_achievement_id' => $achievement->id,
            'reviewer_id' => $reviewer->id,
            'status' => AchievementReviewStatus::Returned->value,
            'notes' => 'Mohon lengkapi bukti.',
        ]);

        $this->assertDatabaseHas(AchievementReview::class, [
            'indicator_achievement_id' => $achievement->id,
            'reviewer_id' => null,
            'status' => AchievementReviewStatus::Pending->value,
            'notes' => null,
            'reviewed_at' => null,
        ]);
    }

    public function test_unit_user_can_view_achievement_detail(): void
    {
        $unit = $this->createUnit('PRD');
        $user = $this->createUnitUser($unit);
        $assignment = $this->createAssignment($unit, 'IKU-001');
        $achievement = IndicatorAchievement::query()->create([
            'assignment_id' => $assignment->id,
            'realization_value' => 87.5,
            'realization_text' => 'Target pembelajaran tercapai sesuai instrumen monitoring.',
            'achievement_status' => AchievementStatus::Achieved,
            'submission_status' => SubmissionStatus::Submitted,
            'submitted_at' => now(),
            'submitted_by' => $user->id,
        ]);

        $this->actingAs($user);

        Livewire::test(ViewIndicatorAchievement::class, ['record' => $achievement->id])
            ->assertOk()
            ->assertSee('Detail Capaian Indikator')
            ->assertSee('Pernyataan IKU-001')
            ->assertSee('Unit PRD')
            ->assertSee('Target pembelajaran tercapai sesuai instrumen monitoring.');
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

    public function test_unit_user_cannot_edit_other_unit_achievement(): void
    {
        $firstUnit = $this->createUnit('PRD');
        $secondUnit = $this->createUnit('LPM');
        $user = $this->createUnitUser($secondUnit);
        $achievement = IndicatorAchievement::query()->create([
            'assignment_id' => $this->createAssignment($firstUnit, 'IKU-001')->id,
            'submission_status' => SubmissionStatus::Draft,
        ]);

        $this->actingAs($user);

        $this->get(IndicatorAchievementResource::getUrl('edit', ['record' => $achievement]))
            ->assertNotFound();
    }

    public function test_indicator_achievement_exposes_standard_indicator_from_assignment(): void
    {
        $unit = $this->createUnit('PRD');
        $assignment = $this->createAssignment($unit, 'IKU-001');
        $achievement = IndicatorAchievement::query()->create([
            'assignment_id' => $assignment->id,
            'submission_status' => SubmissionStatus::Draft,
        ]);

        $this->assertTrue($achievement->standard_indicator->is($assignment->standardIndicator));
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
