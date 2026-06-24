<?php

namespace Tests\Feature;

use App\Enums\QualityStandardStatus;
use App\Enums\SpmiPeriodStatus;
use App\Enums\StandardIndicatorType;
use App\Enums\TargetOperator;
use App\Filament\Resources\QualityStandards\Pages\EditQualityStandard;
use App\Filament\Resources\QualityStandards\RelationManagers\IndicatorsRelationManager;
use App\Models\QualityStandard;
use App\Models\SpmiPeriod;
use App\Models\StandardCategory;
use App\Models\StandardIndicator;
use App\Models\StandardStatement;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class QualityStandardIndicatorsRelationManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $this->actingAs($this->createRoleUser('admin_lpm', [
            'quality-standards.view',
            'quality-standards.update',
            'standard-indicators.view',
            'standard-indicators.create',
            'standard-indicators.update',
            'standard-indicators.delete',
            'quality-documents.view',
            'indicator-assignments.view',
            'indicator-achievements.view',
        ]));
    }

    public function test_indicators_relation_manager_is_rendered_on_quality_standard_edit_page(): void
    {
        $qualityStandard = $this->createQualityStandard();

        Livewire::test(EditQualityStandard::class, ['record' => $qualityStandard->id])
            ->assertSeeLivewire(IndicatorsRelationManager::class);
    }

    public function test_indicators_relation_manager_lists_related_indicators(): void
    {
        $qualityStandard = $this->createQualityStandard();
        $statement = $this->createStandardStatement($qualityStandard);

        $indicator = StandardIndicator::query()->create([
            'quality_standard_id' => $qualityStandard->id,
            'standard_statement_id' => $statement->id,
            'code' => 'IKU-001',
            'statement' => 'Persentase capaian indikator.',
            'indicator_type' => StandardIndicatorType::Percentage,
            'target_value' => 80,
            'target_operator' => TargetOperator::GreaterThanOrEqual,
            'target_unit' => '%',
            'weight' => 1,
            'evidence_required' => true,
            'evidence_description' => 'Dokumen pendukung.',
        ]);

        Livewire::test(IndicatorsRelationManager::class, [
            'ownerRecord' => $qualityStandard,
            'pageClass' => EditQualityStandard::class,
        ])
            ->assertOk()
            ->assertCanSeeTableRecords([$indicator]);
    }

    public function test_indicators_relation_manager_can_create_related_indicator(): void
    {
        $qualityStandard = $this->createQualityStandard();
        $statement = $this->createStandardStatement($qualityStandard);

        Livewire::test(IndicatorsRelationManager::class, [
            'ownerRecord' => $qualityStandard,
            'pageClass' => EditQualityStandard::class,
        ])
            ->callAction(TestAction::make(CreateAction::class)->table(), [
                'code' => 'IKU-002',
                'standard_statement_id' => $statement->id,
                'statement' => 'Jumlah dokumen mutu tersedia.',
                'indicator_type' => StandardIndicatorType::Number->value,
                'target_value' => 5,
                'target_operator' => TargetOperator::GreaterThanOrEqual->value,
                'target_unit' => 'dokumen',
                'weight' => 2,
                'evidence_required' => true,
                'evidence_description' => 'Daftar dokumen mutu.',
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas(StandardIndicator::class, [
            'quality_standard_id' => $qualityStandard->id,
            'standard_statement_id' => $statement->id,
            'code' => 'IKU-002',
            'statement' => 'Jumlah dokumen mutu tersedia.',
        ]);
    }

    private function createQualityStandard(): QualityStandard
    {
        $category = StandardCategory::query()->create([
            'code' => 'STD',
            'name' => 'Standar',
            'description' => null,
        ]);

        return QualityStandard::query()->create([
            'standard_category_id' => $category->id,
            'spmi_period_id' => $this->createSpmiPeriod()->id,
            'code' => 'QS-001',
            'name' => 'Standar Mutu 001',
            'status' => QualityStandardStatus::Draft,
            'version' => 1,
        ]);
    }

    private function createStandardStatement(QualityStandard $qualityStandard): StandardStatement
    {
        return StandardStatement::query()->create([
            'quality_standard_id' => $qualityStandard->id,
            'code' => 'PS-001',
            'statement' => 'Pernyataan standar mutu.',
            'sort_order' => 1,
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
