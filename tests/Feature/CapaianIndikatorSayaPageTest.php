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
use App\Filament\Pages\CapaianIndikatorSaya;
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
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CapaianIndikatorSayaPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_unit_pic_only_sees_own_unit_assignments(): void
    {
        $period = $this->createSpmiPeriod();
        $unit = $this->createUnit('PSI');
        $otherUnit = $this->createUnit('FEB');
        $this->createAssignment($period, $unit, 'IKU-001');
        $this->createAssignment($period, $otherUnit, 'IKU-999');

        $this->actingAs($this->createUnitUser($unit));

        Livewire::test(CapaianIndikatorSaya::class)
            ->assertSee('Capaian Indikator Saya')
            ->assertSee('Unit PSI')
            ->assertSee('IKU-001')
            ->assertDontSee('IKU-999');
    }

    public function test_save_draft_only_affects_clicked_assignment(): void
    {
        $period = $this->createSpmiPeriod();
        $unit = $this->createUnit('PSI');
        $firstAssignment = $this->createAssignment($period, $unit, 'IKU-001');
        $secondAssignment = $this->createAssignment($period, $unit, 'IKU-002');

        $this->actingAs($this->createUnitUser($unit));

        Livewire::test(CapaianIndikatorSaya::class)
            ->call('openAchievementForm', $firstAssignment->id)
            ->set('realizationValue', '85')
            ->set('notes', 'Capaian sementara.')
            ->call('saveDraft')
            ->assertNotified();

        $this->assertDatabaseHas(IndicatorAchievement::class, [
            'assignment_id' => $firstAssignment->id,
            'realization_value' => '85.00',
            'achievement_status' => AchievementStatus::Achieved->value,
            'submission_status' => SubmissionStatus::Draft->value,
            'notes' => 'Capaian sementara.',
        ]);
        $this->assertDatabaseHas(IndicatorUnitAssignment::class, [
            'id' => $firstAssignment->id,
            'status' => IndicatorAssignmentStatus::InProgress->value,
        ]);
        $this->assertDatabaseMissing(IndicatorAchievement::class, [
            'assignment_id' => $secondAssignment->id,
        ]);
        $this->assertDatabaseHas(IndicatorUnitAssignment::class, [
            'id' => $secondAssignment->id,
            'status' => IndicatorAssignmentStatus::Assigned->value,
        ]);
    }

    public function test_submit_requires_evidence_when_indicator_requires_evidence(): void
    {
        $period = $this->createSpmiPeriod();
        $unit = $this->createUnit('PSI');
        $assignment = $this->createAssignment($period, $unit, 'IKU-001', evidenceRequired: true);

        $this->actingAs($this->createUnitUser($unit));

        Livewire::test(CapaianIndikatorSaya::class)
            ->call('openAchievementForm', $assignment->id)
            ->set('realizationValue', '85')
            ->call('submitAchievement')
            ->assertHasErrors(['evidenceFiles']);

        $this->assertDatabaseMissing(IndicatorAchievement::class, [
            'assignment_id' => $assignment->id,
            'submission_status' => SubmissionStatus::Submitted->value,
        ]);
    }

    public function test_submit_with_external_evidence_sends_to_lpm_review(): void
    {
        $period = $this->createSpmiPeriod();
        $unit = $this->createUnit('PSI');
        $assignment = $this->createAssignment($period, $unit, 'IKU-001', evidenceRequired: true);
        $user = $this->createUnitUser($unit);

        $this->actingAs($user);

        Livewire::test(CapaianIndikatorSaya::class)
            ->call('openAchievementForm', $assignment->id)
            ->set('realizationValue', '70')
            ->set('externalUrl', 'https://example.com/bukti')
            ->set('evidenceDescription', 'Dokumen capaian.')
            ->call('submitAchievement')
            ->assertNotified();

        $achievement = IndicatorAchievement::query()->where('assignment_id', $assignment->id)->firstOrFail();

        $this->assertSame(SubmissionStatus::Submitted, $achievement->submission_status);
        $this->assertSame(AchievementStatus::NotAchieved, $achievement->achievement_status);
        $this->assertDatabaseHas(IndicatorUnitAssignment::class, [
            'id' => $assignment->id,
            'status' => IndicatorAssignmentStatus::Submitted->value,
        ]);
        $this->assertDatabaseHas(AchievementEvidence::class, [
            'indicator_achievement_id' => $achievement->id,
            'file_type' => EvidenceFileType::Link->value,
            'external_url' => 'https://example.com/bukti',
            'description' => 'Dokumen capaian.',
            'uploaded_by' => $user->id,
        ]);
        $this->assertDatabaseHas(AchievementReview::class, [
            'indicator_achievement_id' => $achievement->id,
            'status' => AchievementReviewStatus::Pending->value,
        ]);
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

    private function createAssignment(SpmiPeriod $period, Unit $unit, string $indicatorCode, bool $evidenceRequired = true): IndicatorUnitAssignment
    {
        return IndicatorUnitAssignment::query()->create([
            'standard_indicator_id' => $this->createIndicator($period, $indicatorCode, $evidenceRequired)->id,
            'unit_id' => $unit->id,
            'spmi_period_id' => $period->id,
            'due_date' => '2026-12-31',
            'status' => IndicatorAssignmentStatus::Assigned,
            'is_primary_pic' => true,
            'priority' => IndicatorAssignmentPriority::Normal,
        ]);
    }

    private function createIndicator(SpmiPeriod $period, string $code, bool $evidenceRequired): StandardIndicator
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
            'evidence_required' => $evidenceRequired,
            'evidence_description' => 'Dokumen pendukung.',
        ]);
    }
}
