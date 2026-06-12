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
use App\Filament\Resources\AchievementReviews\Pages\ListAchievementReviews;
use App\Filament\Resources\AchievementReviews\Pages\ViewAchievementReview;
use App\Filament\Widgets\LpmAchievementReviewQueue;
use App\Models\AchievementEvidence;
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

class AchievementReviewResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_submitted_achievement_appears_in_validation_page_and_lpm_dashboard_widget(): void
    {
        $reviewer = $this->createReviewer();
        $achievement = $this->createSubmittedAchievement($this->createUnit('PRD'), 'IKU-000');
        $review = $this->createPendingReview($achievement);

        $this->actingAs($reviewer);

        Livewire::test(ListAchievementReviews::class)
            ->assertCanSeeTableRecords([$review])
            ->assertSee('Pernyataan IKU-000');

        Livewire::test(LpmAchievementReviewQueue::class)
            ->assertCanSeeTableRecords([$review])
            ->assertSee('Pernyataan IKU-000');
    }

    public function test_lpm_user_can_validate_submitted_achievement(): void
    {
        $reviewer = $this->createReviewer();
        $achievement = $this->createSubmittedAchievement($this->createUnit('PRD'), 'IKU-001');
        $review = $this->createPendingReview($achievement);

        $this->actingAs($reviewer);

        Livewire::test(ListAchievementReviews::class)
            ->callAction(TestAction::make('validateAchievement')->table($review), [
                'notes' => 'Data dan bukti sesuai.',
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas(AchievementReview::class, [
            'indicator_achievement_id' => $achievement->id,
            'reviewer_id' => $reviewer->id,
            'status' => AchievementReviewStatus::Validated->value,
            'notes' => 'Data dan bukti sesuai.',
        ]);

        $this->assertDatabaseHas(IndicatorAchievement::class, [
            'id' => $achievement->id,
            'submission_status' => SubmissionStatus::Validated->value,
        ]);

        $this->assertDatabaseHas(IndicatorUnitAssignment::class, [
            'id' => $achievement->assignment_id,
            'status' => IndicatorAssignmentStatus::Validated->value,
        ]);
    }

    public function test_lpm_user_validating_achievement_updates_pending_review(): void
    {
        $reviewer = $this->createReviewer();
        $achievement = $this->createSubmittedAchievement($this->createUnit('PRD'), 'IKU-009');

        $review = $this->createPendingReview($achievement);

        $this->actingAs($reviewer);

        Livewire::test(ListAchievementReviews::class)
            ->callAction(TestAction::make('validateAchievement')->table($review), [
                'notes' => 'Data dan bukti sudah sesuai.',
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseCount((new AchievementReview)->getTable(), 1);

        $review = AchievementReview::query()->sole();

        $this->assertTrue($achievement->is($review->achievement));
        $this->assertTrue($reviewer->is($review->reviewer));
        $this->assertSame(AchievementReviewStatus::Validated, $review->status);
        $this->assertSame('Data dan bukti sudah sesuai.', $review->notes);
        $this->assertNotNull($review->reviewed_at);

        $this->assertDatabaseHas(IndicatorAchievement::class, [
            'id' => $achievement->id,
            'submission_status' => SubmissionStatus::Validated->value,
        ]);

        $this->assertDatabaseHas(IndicatorUnitAssignment::class, [
            'id' => $achievement->assignment_id,
            'status' => IndicatorAssignmentStatus::Validated->value,
        ]);
    }

    public function test_lpm_user_can_return_submitted_achievement_with_required_notes(): void
    {
        $reviewer = $this->createReviewer();
        $achievement = $this->createSubmittedAchievement($this->createUnit('PRD'), 'IKU-002');
        $review = $this->createPendingReview($achievement);

        $this->actingAs($reviewer);

        Livewire::test(ListAchievementReviews::class)
            ->callAction(TestAction::make('returnAchievement')->table($review), [
                'notes' => 'Mohon lengkapi bukti pendukung.',
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas(AchievementReview::class, [
            'indicator_achievement_id' => $achievement->id,
            'reviewer_id' => $reviewer->id,
            'status' => AchievementReviewStatus::Returned->value,
            'notes' => 'Mohon lengkapi bukti pendukung.',
        ]);

        $this->assertDatabaseHas(IndicatorAchievement::class, [
            'id' => $achievement->id,
            'submission_status' => SubmissionStatus::Returned->value,
        ]);

        $this->assertDatabaseHas(IndicatorUnitAssignment::class, [
            'id' => $achievement->assignment_id,
            'status' => IndicatorAssignmentStatus::Returned->value,
        ]);
    }

    public function test_lpm_user_can_reject_submitted_achievement_and_submission_falls_back_to_returned(): void
    {
        $reviewer = $this->createReviewer();
        $achievement = $this->createSubmittedAchievement($this->createUnit('PRD'), 'IKU-003');
        $review = $this->createPendingReview($achievement);

        $this->actingAs($reviewer);

        Livewire::test(ListAchievementReviews::class)
            ->callAction(TestAction::make('rejectAchievement')->table($review), [
                'notes' => 'Data realisasi tidak dapat diterima.',
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas(AchievementReview::class, [
            'indicator_achievement_id' => $achievement->id,
            'reviewer_id' => $reviewer->id,
            'status' => AchievementReviewStatus::Rejected->value,
            'notes' => 'Data realisasi tidak dapat diterima.',
        ]);

        $this->assertDatabaseHas(IndicatorAchievement::class, [
            'id' => $achievement->id,
            'submission_status' => SubmissionStatus::Returned->value,
        ]);

        $this->assertDatabaseHas(IndicatorUnitAssignment::class, [
            'id' => $achievement->assignment_id,
            'status' => IndicatorAssignmentStatus::Returned->value,
        ]);
    }

    public function test_unit_pic_can_view_own_review_result_but_cannot_validate(): void
    {
        $unit = $this->createUnit('PRD');
        $otherUnit = $this->createUnit('LPM');
        $unitUser = $this->createUnitPic($unit);
        $ownAchievement = $this->createSubmittedAchievement($unit, 'IKU-004');
        $otherAchievement = $this->createSubmittedAchievement($otherUnit, 'IKU-005');
        $ownReview = $this->createPendingReview($ownAchievement);
        $otherReview = $this->createPendingReview($otherAchievement);

        $this->actingAs($unitUser);

        Livewire::test(ListAchievementReviews::class)
            ->assertCanSeeTableRecords([$ownReview])
            ->assertCanNotSeeTableRecords([$otherReview])
            ->assertActionHidden(TestAction::make('validateAchievement')->table($ownReview))
            ->assertActionHidden(TestAction::make('returnAchievement')->table($ownReview))
            ->assertActionHidden(TestAction::make('rejectAchievement')->table($ownReview));
    }

    public function test_pimpinan_can_view_all_achievements_but_cannot_validate(): void
    {
        $pimpinan = $this->createPimpinan();
        $firstAchievement = $this->createSubmittedAchievement($this->createUnit('PRD'), 'IKU-007');
        $secondAchievement = $this->createSubmittedAchievement($this->createUnit('LPM'), 'IKU-008');
        $firstReview = $this->createPendingReview($firstAchievement);
        $secondReview = $this->createPendingReview($secondAchievement);

        $this->actingAs($pimpinan);

        Livewire::test(ListAchievementReviews::class)
            ->assertCanSeeTableRecords([$firstReview, $secondReview])
            ->assertActionHidden(TestAction::make('validateAchievement')->table($firstReview))
            ->assertActionHidden(TestAction::make('returnAchievement')->table($firstReview))
            ->assertActionHidden(TestAction::make('rejectAchievement')->table($firstReview));
    }

    public function test_detail_view_shows_evidence_and_review_history(): void
    {
        $reviewer = $this->createReviewer();
        $achievement = $this->createSubmittedAchievement($this->createUnit('PRD'), 'IKU-006');
        $pendingReview = $this->createPendingReview($achievement);

        AchievementEvidence::query()->create([
            'indicator_achievement_id' => $achievement->id,
            'file_type' => EvidenceFileType::Link,
            'external_url' => 'https://example.com/bukti',
            'description' => 'Folder bukti validasi.',
            'uploaded_by' => $achievement->submitted_by,
        ]);

        AchievementReview::query()->create([
            'indicator_achievement_id' => $achievement->id,
            'reviewer_id' => $reviewer->id,
            'status' => AchievementReviewStatus::Returned,
            'notes' => 'Catatan review sebelumnya.',
            'reviewed_at' => now(),
        ]);

        $this->actingAs($reviewer);

        Livewire::test(ViewAchievementReview::class, ['record' => $pendingReview->id])
            ->assertOk()
            ->assertSee('Detail Validasi Capaian')
            ->assertSee('Pernyataan IKU-006')
            ->assertSee('Folder bukti validasi.')
            ->assertSee('Catatan review sebelumnya.');
    }

    private function createReviewer(): User
    {
        $permissions = [
            'indicator-achievements.view',
            'indicator-achievements.review',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $role = Role::findOrCreate('admin_lpm', 'web');
        $role->syncPermissions($permissions);

        return User::factory()->create()->assignRole($role);
    }

    private function createUnitPic(Unit $unit): User
    {
        Permission::findOrCreate('indicator-achievements.view', 'web');

        $role = Role::findOrCreate('unit_pic', 'web');
        $role->syncPermissions(['indicator-achievements.view']);

        return User::factory()
            ->create([
                'unit_id' => $unit->id,
            ])
            ->assignRole($role);
    }

    private function createPimpinan(): User
    {
        Permission::findOrCreate('indicator-achievements.view', 'web');

        $role = Role::findOrCreate('pimpinan', 'web');
        $role->syncPermissions(['indicator-achievements.view']);

        return User::factory()->create()->assignRole($role);
    }

    private function createSubmittedAchievement(Unit $unit, string $indicatorCode): IndicatorAchievement
    {
        $submitter = User::factory()
            ->create([
                'unit_id' => $unit->id,
            ]);

        return IndicatorAchievement::query()->create([
            'assignment_id' => $this->createAssignment($unit, $indicatorCode)->id,
            'realization_value' => 87.5,
            'realization_text' => "Realisasi {$indicatorCode}",
            'achievement_status' => AchievementStatus::Achieved,
            'notes' => "Catatan {$indicatorCode}",
            'submission_status' => SubmissionStatus::Submitted,
            'submitted_at' => now(),
            'submitted_by' => $submitter->id,
        ]);
    }

    private function createPendingReview(IndicatorAchievement $achievement): AchievementReview
    {
        return AchievementReview::query()->create([
            'indicator_achievement_id' => $achievement->id,
            'reviewer_id' => null,
            'status' => AchievementReviewStatus::Pending,
            'notes' => null,
            'reviewed_at' => null,
        ]);
    }

    private function createAssignment(Unit $unit, string $indicatorCode): IndicatorUnitAssignment
    {
        return IndicatorUnitAssignment::query()->create([
            'standard_indicator_id' => $this->createIndicator($indicatorCode)->id,
            'unit_id' => $unit->id,
            'spmi_period_id' => $this->createSpmiPeriod()->id,
            'due_date' => '2026-06-30',
            'status' => IndicatorAssignmentStatus::Submitted,
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
