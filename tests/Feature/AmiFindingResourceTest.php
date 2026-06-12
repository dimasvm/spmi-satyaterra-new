<?php

namespace Tests\Feature;

use App\Enums\AmiAssessmentResult;
use App\Enums\AmiAuditorRole;
use App\Enums\AmiAuditStatus;
use App\Enums\AmiFindingCategory;
use App\Enums\AmiFindingStatus;
use App\Enums\AmiPeriodStatus;
use App\Enums\QualityStandardStatus;
use App\Enums\SpmiPeriodStatus;
use App\Enums\StandardIndicatorType;
use App\Enums\TargetOperator;
use App\Filament\Resources\AmiChecklists\Pages\ListAmiChecklists;
use App\Filament\Resources\AmiFindings\Pages\ListAmiFindings;
use App\Models\AmiAudit;
use App\Models\AmiAuditor;
use App\Models\AmiChecklist;
use App\Models\AmiFinding;
use App\Models\AmiPeriod;
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
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AmiFindingResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_assigned_auditor_can_create_finding_from_non_conform_checklist(): void
    {
        [$audit, $checklist, $auditor] = $this->createChecklistDataset(AmiAssessmentResult::Minor, 'SI');

        $this->actingAs($auditor);

        Livewire::test(ListAmiChecklists::class)
            ->callAction(TestAction::make('createFinding')->table($checklist), [
                'category' => AmiFindingCategory::Minor->value,
                'description' => 'Dokumen pelaksanaan belum lengkap.',
                'recommendation' => 'Lengkapi dokumen pelaksanaan.',
                'due_date' => '2026-07-30',
                'status' => AmiFindingStatus::Open->value,
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas(AmiFinding::class, [
            'ami_audit_id' => $audit->id,
            'ami_checklist_id' => $checklist->id,
            'standard_indicator_id' => $checklist->standard_indicator_id,
            'finding_number' => 'AMI-2026-SI-001',
            'category' => AmiFindingCategory::Minor->value,
            'description' => 'Dokumen pelaksanaan belum lengkap.',
            'recommendation' => 'Lengkapi dokumen pelaksanaan.',
            'due_date' => '2026-07-30 00:00:00',
            'status' => AmiFindingStatus::Open->value,
            'created_by' => $auditor->id,
        ]);
    }

    public function test_minor_finding_requires_recommendation_and_due_date(): void
    {
        [, $checklist, $auditor] = $this->createChecklistDataset(AmiAssessmentResult::Minor, 'SI');

        $this->actingAs($auditor);

        Livewire::test(ListAmiChecklists::class)
            ->callAction(TestAction::make('createFinding')->table($checklist), [
                'category' => AmiFindingCategory::Minor->value,
                'description' => 'Ada ketidaksesuaian minor.',
                'status' => AmiFindingStatus::Open->value,
            ])
            ->assertHasActionErrors([
                'recommendation' => 'required',
                'due_date' => 'required',
            ]);
    }

    public function test_conform_checklist_hides_create_finding_action(): void
    {
        [, $checklist, $auditor] = $this->createChecklistDataset(AmiAssessmentResult::Conform, 'SI');

        $this->actingAs($auditor);

        Livewire::test(ListAmiChecklists::class)
            ->assertActionHidden(TestAction::make('createFinding')->table($checklist));
    }

    public function test_unit_pic_only_sees_own_findings_after_audit_finalized(): void
    {
        [$finalAudit, $finalChecklist] = $this->createChecklistDataset(AmiAssessmentResult::Major, 'SI', AmiAuditStatus::Finalized);
        [$draftAudit, $draftChecklist] = $this->createChecklistDataset(AmiAssessmentResult::Major, 'AK', AmiAuditStatus::Ongoing);
        [$otherAudit, $otherChecklist] = $this->createChecklistDataset(AmiAssessmentResult::Major, 'MK', AmiAuditStatus::Finalized);

        $unitUser = $this->createRoleUser('unit_pic', $finalAudit->auditee_unit_id);
        $visibleFinding = $this->createFinding($finalAudit, $finalChecklist);
        $notFinalFinding = $this->createFinding($draftAudit, $draftChecklist);
        $otherUnitFinding = $this->createFinding($otherAudit, $otherChecklist);

        $this->actingAs($unitUser);

        Livewire::test(ListAmiFindings::class)
            ->assertCanSeeTableRecords([$visibleFinding])
            ->assertCanNotSeeTableRecords([$notFinalFinding, $otherUnitFinding]);
    }

    private function createChecklistDataset(
        AmiAssessmentResult $assessmentResult,
        string $unitCode,
        AmiAuditStatus $auditStatus = AmiAuditStatus::Ongoing,
    ): array {
        $unit = $this->createUnit($unitCode);
        $audit = $this->createAudit($unit, $auditStatus);
        $indicator = $this->createIndicator("IKU-{$unitCode}");
        $checklist = AmiChecklist::query()->create([
            'ami_audit_id' => $audit->id,
            'standard_indicator_id' => $indicator->id,
            'assessment_result' => $assessmentResult,
            'auditor_notes' => 'Catatan audit.',
        ]);
        $auditor = $this->createRoleUser('auditor');

        AmiAuditor::query()->create([
            'ami_audit_id' => $audit->id,
            'user_id' => $auditor->id,
            'role' => AmiAuditorRole::Lead,
        ]);

        return [$audit, $checklist, $auditor];
    }

    private function createFinding(AmiAudit $audit, AmiChecklist $checklist): AmiFinding
    {
        return AmiFinding::query()->create([
            'ami_audit_id' => $audit->id,
            'ami_checklist_id' => $checklist->id,
            'standard_indicator_id' => $checklist->standard_indicator_id,
            'category' => AmiFindingCategory::Major,
            'description' => 'Temuan mayor.',
            'recommendation' => 'Segera lakukan tindakan korektif.',
            'due_date' => '2026-08-01',
        ]);
    }

    private function createAudit(Unit $unit, AmiAuditStatus $status): AmiAudit
    {
        $spmiPeriod = SpmiPeriod::query()->create([
            'name' => 'SPMI 2026 '.$unit->code,
            'academic_year' => '2025/2026',
            'semester' => null,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => SpmiPeriodStatus::Active,
        ]);
        $amiPeriod = AmiPeriod::query()->create([
            'spmi_period_id' => $spmiPeriod->id,
            'name' => 'AMI 2026 '.$unit->code,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => AmiPeriodStatus::Ongoing,
        ]);

        return AmiAudit::query()->create([
            'ami_period_id' => $amiPeriod->id,
            'auditee_unit_id' => $unit->id,
            'scheduled_date' => '2026-06-01',
            'status' => $status,
            'finalized_at' => $status === AmiAuditStatus::Finalized ? now() : null,
        ]);
    }

    private function createIndicator(string $code): StandardIndicator
    {
        $category = StandardCategory::query()->firstOrCreate(
            ['code' => 'STD'],
            ['name' => 'Standar', 'description' => null],
        );
        $qualityStandard = QualityStandard::query()->create([
            'standard_category_id' => $category->id,
            'code' => 'QS-'.$code,
            'name' => 'Standar Mutu '.$code,
            'status' => QualityStandardStatus::Draft,
            'version' => 1,
        ]);

        return StandardIndicator::query()->create([
            'quality_standard_id' => $qualityStandard->id,
            'code' => $code,
            'statement' => 'Pernyataan '.$code,
            'indicator_type' => StandardIndicatorType::Percentage,
            'target_value' => 80,
            'target_operator' => TargetOperator::GreaterThanOrEqual,
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
            'name' => 'Unit '.$code,
            'type' => null,
            'is_active' => true,
        ]);
    }

    private function createRoleUser(string $roleName, ?int $unitId = null): User
    {
        $role = Role::findOrCreate($roleName, 'web');

        return User::factory()
            ->create([
                'unit_id' => $unitId,
            ])
            ->assignRole($role);
    }
}
