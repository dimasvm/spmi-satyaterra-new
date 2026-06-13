<?php

namespace Tests\Feature;

use App\Enums\AchievementReviewStatus;
use App\Enums\AchievementStatus;
use App\Enums\EvidenceFileType;
use App\Enums\IndicatorAssignmentPriority;
use App\Enums\IndicatorAssignmentStatus;
use App\Enums\QualityStandardStatus;
use App\Enums\SpmiPeriodStatus;
use App\Enums\StandardIndicatorType;
use App\Enums\SubmissionStatus;
use App\Filament\Pages\InboxValidasiCapaian;
use App\Models\AchievementReview;
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

class InboxValidasiCapaianPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_admin_lpm_can_render_inbox_and_see_submitted_achievement(): void
    {
        $period = $this->createSpmiPeriod();
        $unit = $this->createUnit('PSI');
        $achievement = $this->createSubmittedAchievement($period, $unit, 'IKU-001');

        $this->actingAs($this->createAdminLpm());

        Livewire::test(InboxValidasiCapaian::class)
            ->assertSee('Inbox Validasi Capaian')
            ->assertSee('Menunggu Validasi')
            ->assertSee('Unit PSI')
            ->assertSee('IKU-001')
            ->assertSee((string) $achievement->evidences()->count());
    }

    public function test_validate_action_updates_review_achievement_and_assignment(): void
    {
        $period = $this->createSpmiPeriod();
        $unit = $this->createUnit('PSI');
        $achievement = $this->createSubmittedAchievement($period, $unit, 'IKU-001');
        $admin = $this->createAdminLpm();

        $this->actingAs($admin);

        Livewire::test(InboxValidasiCapaian::class)
            ->call('openReview', $achievement->id)
            ->call('validateAchievement')
            ->assertNotified();

        $this->assertDatabaseHas(IndicatorAchievement::class, [
            'id' => $achievement->id,
            'submission_status' => SubmissionStatus::Validated->value,
        ]);
        $this->assertDatabaseHas(IndicatorUnitAssignment::class, [
            'id' => $achievement->assignment_id,
            'status' => IndicatorAssignmentStatus::Validated->value,
        ]);
        $this->assertDatabaseHas(AchievementReview::class, [
            'indicator_achievement_id' => $achievement->id,
            'reviewer_id' => $admin->id,
            'status' => AchievementReviewStatus::Validated->value,
        ]);
    }

    public function test_return_action_requires_notes(): void
    {
        $period = $this->createSpmiPeriod();
        $unit = $this->createUnit('PSI');
        $achievement = $this->createSubmittedAchievement($period, $unit, 'IKU-001');

        $this->actingAs($this->createAdminLpm());

        Livewire::test(InboxValidasiCapaian::class)
            ->call('openReview', $achievement->id)
            ->call('returnAchievement')
            ->assertHasErrors(['reviewNotes' => 'required']);

        $this->assertDatabaseHas(IndicatorAchievement::class, [
            'id' => $achievement->id,
            'submission_status' => SubmissionStatus::Submitted->value,
        ]);
    }

    public function test_return_action_sends_back_for_revision(): void
    {
        $period = $this->createSpmiPeriod();
        $unit = $this->createUnit('PSI');
        $achievement = $this->createSubmittedAchievement($period, $unit, 'IKU-001');
        $admin = $this->createAdminLpm();

        $this->actingAs($admin);

        Livewire::test(InboxValidasiCapaian::class)
            ->call('openReview', $achievement->id)
            ->set('reviewNotes', 'Lengkapi bukti pendukung.')
            ->call('returnAchievement')
            ->assertNotified();

        $this->assertDatabaseHas(IndicatorAchievement::class, [
            'id' => $achievement->id,
            'submission_status' => SubmissionStatus::Returned->value,
        ]);
        $this->assertDatabaseHas(IndicatorUnitAssignment::class, [
            'id' => $achievement->assignment_id,
            'status' => IndicatorAssignmentStatus::Returned->value,
        ]);
        $this->assertDatabaseHas(AchievementReview::class, [
            'indicator_achievement_id' => $achievement->id,
            'reviewer_id' => $admin->id,
            'status' => AchievementReviewStatus::Returned->value,
            'notes' => 'Lengkapi bukti pendukung.',
        ]);
    }

    public function test_reject_action_records_rejected_review_and_returns_submission(): void
    {
        $period = $this->createSpmiPeriod();
        $unit = $this->createUnit('PSI');
        $achievement = $this->createSubmittedAchievement($period, $unit, 'IKU-001');

        $this->actingAs($this->createAdminLpm());

        Livewire::test(InboxValidasiCapaian::class)
            ->call('openReview', $achievement->id)
            ->set('reviewNotes', 'Data tidak dapat diterima.')
            ->call('rejectAchievement')
            ->assertNotified();

        $this->assertDatabaseHas(IndicatorAchievement::class, [
            'id' => $achievement->id,
            'submission_status' => SubmissionStatus::Returned->value,
        ]);
        $this->assertDatabaseHas(AchievementReview::class, [
            'indicator_achievement_id' => $achievement->id,
            'status' => AchievementReviewStatus::Rejected->value,
            'notes' => 'Data tidak dapat diterima.',
        ]);
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

    private function createSubmittedAchievement(SpmiPeriod $period, Unit $unit, string $indicatorCode): IndicatorAchievement
    {
        $assignment = IndicatorUnitAssignment::query()->create([
            'standard_indicator_id' => $this->createIndicator($period, $indicatorCode)->id,
            'unit_id' => $unit->id,
            'spmi_period_id' => $period->id,
            'due_date' => '2026-12-31',
            'status' => IndicatorAssignmentStatus::Submitted,
            'is_primary_pic' => true,
            'priority' => IndicatorAssignmentPriority::Normal,
        ]);

        $submitter = User::factory()->create(['unit_id' => $unit->id]);

        $achievement = IndicatorAchievement::query()->create([
            'assignment_id' => $assignment->id,
            'realization_value' => 85,
            'achievement_status' => AchievementStatus::Achieved,
            'submission_status' => SubmissionStatus::Submitted,
            'submitted_at' => now(),
            'submitted_by' => $submitter->id,
            'notes' => 'Capaian sudah lengkap.',
        ]);

        $achievement->evidences()->create([
            'file_type' => EvidenceFileType::Link,
            'external_url' => 'https://example.com/bukti',
            'description' => 'Dokumen pendukung.',
            'uploaded_by' => $submitter->id,
        ]);

        $achievement->reviews()->create([
            'reviewer_id' => null,
            'status' => AchievementReviewStatus::Pending,
            'notes' => null,
            'reviewed_at' => null,
        ]);

        return $achievement;
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
