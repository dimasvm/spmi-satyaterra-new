<?php

namespace Tests\Feature;

use App\Enums\QualityStandardStatus;
use App\Enums\StandardIndicatorType;
use App\Enums\TargetOperator;
use App\Filament\Resources\QualityStandards\Pages\EditQualityStandard;
use App\Filament\Resources\QualityStandards\RelationManagers\IndicatorsRelationManager;
use App\Models\QualityStandard;
use App\Models\StandardCategory;
use App\Models\StandardIndicator;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class QualityStandardIndicatorsRelationManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $this->actingAs(User::factory()->create());
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

        $indicator = StandardIndicator::query()->create([
            'quality_standard_id' => $qualityStandard->id,
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

        Livewire::test(IndicatorsRelationManager::class, [
            'ownerRecord' => $qualityStandard,
            'pageClass' => EditQualityStandard::class,
        ])
            ->callAction(TestAction::make(CreateAction::class)->table(), [
                'code' => 'IKU-002',
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
            'code' => 'QS-001',
            'name' => 'Standar Mutu 001',
            'status' => QualityStandardStatus::Draft,
            'version' => 1,
        ]);
    }
}
