<?php

namespace Tests\Feature;

use App\Enums\AchievementReviewStatus;
use App\Enums\AchievementStatus;
use App\Enums\IndicatorAssignmentStatus;
use App\Enums\QualityDocumentStatus;
use App\Enums\QualityDocumentType;
use App\Enums\QualityStandardStatus;
use App\Enums\SpmiPeriodStatus;
use App\Enums\StandardIndicatorType;
use App\Enums\SubmissionStatus;
use App\Filament\Resources\QualityStandards\Pages\EditQualityStandard;
use App\Filament\Resources\QualityStandards\Pages\ViewQualityStandard;
use App\Filament\Resources\QualityStandards\RelationManagers\AchievementsRelationManager;
use App\Filament\Resources\QualityStandards\RelationManagers\AssignmentsRelationManager;
use App\Filament\Resources\QualityStandards\RelationManagers\DocumentsRelationManager;
use App\Models\AchievementReview;
use App\Models\IndicatorAchievement;
use App\Models\IndicatorUnitAssignment;
use App\Models\QualityDocument;
use App\Models\QualityStandard;
use App\Models\SpmiPeriod;
use App\Models\StandardCategory;
use App\Models\StandardIndicator;
use App\Models\Unit;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class QualityStandardHubRelationManagersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_quality_standard_view_page_shows_complete_infolist(): void
    {
        $admin = $this->createRoleUser('admin_lpm', [
            'quality-standards.view',
            'quality-standards.update',
            'standard-indicators.view',
            'quality-documents.view',
            'indicator-assignments.view',
            'indicator-achievements.view',
        ]);
        $standard = $this->createQualityStandard([
            'name' => 'Standar Kompetensi Lulusan',
            'description' => 'Deskripsi standar mutu.',
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        $this->actingAs($admin);

        Livewire::test(ViewQualityStandard::class, ['record' => $standard->id])
            ->assertOk()
            ->assertSee('Detail Standar')
            ->assertSee('QS-001')
            ->assertSee('Standar Kompetensi Lulusan')
            ->assertSee('Deskripsi standar mutu.');
    }

    public function test_documents_relation_manager_create_fills_standard_period_and_uploader(): void
    {
        $admin = $this->createRoleUser('admin_lpm', [
            'quality-documents.view',
            'quality-documents.create',
        ]);
        $standard = $this->createQualityStandard();

        $this->actingAs($admin);

        Livewire::test(DocumentsRelationManager::class, [
            'ownerRecord' => $standard,
            'pageClass' => EditQualityStandard::class,
        ])
            ->callAction(TestAction::make(CreateAction::class)->table(), [
                'title' => 'Manual Mutu Akademik',
                'document_type' => QualityDocumentType::Manual->value,
                'document_number' => 'MM-001',
                'version' => 1,
                'external_url' => 'https://example.com/manual.pdf',
                'status' => QualityDocumentStatus::Draft->value,
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas(QualityDocument::class, [
            'quality_standard_id' => $standard->id,
            'spmi_period_id' => $standard->spmi_period_id,
            'title' => 'Manual Mutu Akademik',
            'uploaded_by' => $admin->id,
        ]);
    }

    public function test_assignments_relation_manager_only_lists_assignments_for_owner_standard(): void
    {
        $admin = $this->createRoleUser('admin_lpm', [
            'indicator-assignments.view',
            'indicator-assignments.update',
            'indicator-assignments.delete',
        ]);
        [$standard, $otherStandard] = [$this->createQualityStandard(), $this->createQualityStandard(['code' => 'QS-002'])];
        $assignment = $this->createAssignment($this->createIndicator($standard, 'IKU-001'), $this->createUnit('PRD'));
        $otherAssignment = $this->createAssignment($this->createIndicator($otherStandard, 'IKU-002'), $this->createUnit('LPM'));
        IndicatorAchievement::query()->create([
            'assignment_id' => $assignment->id,
            'submission_status' => SubmissionStatus::Draft,
        ]);

        $this->actingAs($admin);

        Livewire::test(AssignmentsRelationManager::class, [
            'ownerRecord' => $standard,
            'pageClass' => EditQualityStandard::class,
        ])
            ->assertCanSeeTableRecords([$assignment])
            ->assertCanNotSeeTableRecords([$otherAssignment])
            ->assertActionHidden(TestAction::make(DeleteAction::class)->table($assignment));
    }

    public function test_achievements_relation_manager_lists_standard_achievements_and_review_status(): void
    {
        $admin = $this->createRoleUser('admin_lpm', [
            'indicator-achievements.view',
        ]);
        [$standard, $otherStandard] = [$this->createQualityStandard(), $this->createQualityStandard(['code' => 'QS-002'])];
        $achievement = $this->createAchievement($this->createAssignment($this->createIndicator($standard, 'IKU-001'), $this->createUnit('PRD')));
        $otherAchievement = $this->createAchievement($this->createAssignment($this->createIndicator($otherStandard, 'IKU-002'), $this->createUnit('LPM')));

        AchievementReview::query()->create([
            'indicator_achievement_id' => $achievement->id,
            'status' => AchievementReviewStatus::Validated,
            'reviewed_at' => now(),
        ]);

        $this->actingAs($admin);

        Livewire::test(AchievementsRelationManager::class, [
            'ownerRecord' => $standard,
            'pageClass' => EditQualityStandard::class,
        ])
            ->assertCanSeeTableRecords([$achievement])
            ->assertCanNotSeeTableRecords([$otherAchievement])
            ->assertSee('Tervalidasi');
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createQualityStandard(array $overrides = []): QualityStandard
    {
        $category = StandardCategory::query()->firstOrCreate(
            ['code' => 'STD'],
            ['name' => 'Standar', 'description' => null],
        );

        return QualityStandard::query()->create([
            'standard_category_id' => $category->id,
            'spmi_period_id' => $overrides['spmi_period_id'] ?? $this->createSpmiPeriod()->id,
            'code' => $overrides['code'] ?? 'QS-001',
            'name' => $overrides['name'] ?? 'Standar Mutu',
            'description' => $overrides['description'] ?? null,
            'status' => $overrides['status'] ?? QualityStandardStatus::Draft,
            'version' => $overrides['version'] ?? 1,
            'approved_by' => $overrides['approved_by'] ?? null,
            'approved_at' => $overrides['approved_at'] ?? null,
        ]);
    }

    private function createIndicator(QualityStandard $standard, string $code): StandardIndicator
    {
        return StandardIndicator::query()->create([
            'quality_standard_id' => $standard->id,
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

    private function createAssignment(StandardIndicator $indicator, Unit $unit): IndicatorUnitAssignment
    {
        return IndicatorUnitAssignment::query()->create([
            'standard_indicator_id' => $indicator->id,
            'unit_id' => $unit->id,
            'spmi_period_id' => $indicator->qualityStandard->spmi_period_id,
            'due_date' => '2026-06-30',
            'status' => IndicatorAssignmentStatus::Assigned,
            'is_primary_pic' => true,
            'priority' => 'normal',
        ]);
    }

    private function createAchievement(IndicatorUnitAssignment $assignment): IndicatorAchievement
    {
        return IndicatorAchievement::query()->create([
            'assignment_id' => $assignment->id,
            'realization_value' => 90,
            'achievement_status' => AchievementStatus::Achieved,
            'submission_status' => SubmissionStatus::Submitted,
            'submitted_at' => now(),
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
            'name' => 'SPMI '.fake()->unique()->year(),
            'academic_year' => '2025/2026',
            'semester' => null,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => SpmiPeriodStatus::Active,
        ]);
    }

    /**
     * @param  array<int, string>  $permissions
     */
    private function createRoleUser(string $roleName, array $permissions): User
    {
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $role = Role::findOrCreate($roleName, 'web');
        $role->syncPermissions($permissions);

        return User::factory()->create()->assignRole($role);
    }
}
