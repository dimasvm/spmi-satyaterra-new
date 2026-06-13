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
use App\Filament\Pages\AuditSaya;
use App\Filament\Pages\AuditWorkspace;
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
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuditWorkspacePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_audit_saya_only_lists_assigned_audits_for_auditor(): void
    {
        [$assignedAudit, , $auditor] = $this->createChecklistDataset('SI', AmiAuditorRole::Lead);
        [$otherAudit] = $this->createChecklistDataset('AK', AmiAuditorRole::Lead);

        $this->actingAs($auditor);

        Livewire::test(AuditSaya::class)
            ->assertSee($assignedAudit->auditeeUnit->name)
            ->assertDontSee($otherAudit->auditeeUnit->name);
    }

    public function test_unassigned_auditor_cannot_open_audit_workspace(): void
    {
        [$audit] = $this->createChecklistDataset('SI', AmiAuditorRole::Lead);
        $otherAuditor = $this->createRoleUser('auditor');

        $this->actingAs($otherAuditor);

        Livewire::test(AuditWorkspace::class, ['audit' => $audit->id])
            ->assertForbidden();
    }

    public function test_lead_auditor_can_save_assessment_create_finding_and_finalize(): void
    {
        [$audit, $checklist, $auditor] = $this->createChecklistDataset('SI', AmiAuditorRole::Lead);

        $this->actingAs($auditor);

        Livewire::test(AuditWorkspace::class, ['audit' => $audit->id])
            ->call('openAssessment', $checklist->id)
            ->set('assessmentResult', AmiAssessmentResult::Minor->value)
            ->set('auditorNotes', 'Dokumen belum lengkap.')
            ->call('saveAssessment')
            ->assertNotified()
            ->call('openFindingFromChecklist', $checklist->id)
            ->set('findingDescription', 'Dokumen pelaksanaan belum lengkap.')
            ->set('findingRecommendation', 'Lengkapi dokumen pelaksanaan.')
            ->set('findingDueDate', '2026-07-30')
            ->call('saveFinding')
            ->assertNotified()
            ->call('finalizeAudit')
            ->assertNotified();

        $this->assertDatabaseHas(AmiChecklist::class, [
            'id' => $checklist->id,
            'assessment_result' => AmiAssessmentResult::Minor->value,
            'auditor_notes' => 'Dokumen belum lengkap.',
        ]);
        $this->assertDatabaseHas(AmiFinding::class, [
            'ami_audit_id' => $audit->id,
            'ami_checklist_id' => $checklist->id,
            'category' => AmiFindingCategory::Minor->value,
            'description' => 'Dokumen pelaksanaan belum lengkap.',
            'recommendation' => 'Lengkapi dokumen pelaksanaan.',
            'status' => AmiFindingStatus::Open->value,
        ]);
        $this->assertDatabaseHas(AmiAudit::class, [
            'id' => $audit->id,
            'status' => AmiAuditStatus::Finalized->value,
            'finalized_by' => $auditor->id,
        ]);
    }

    public function test_observer_can_view_but_cannot_save_assessment(): void
    {
        [$audit, $checklist, $observer] = $this->createChecklistDataset('SI', AmiAuditorRole::Observer);

        $this->actingAs($observer);

        Livewire::test(AuditWorkspace::class, ['audit' => $audit->id])
            ->assertSee($audit->auditeeUnit->name)
            ->call('openAssessment', $checklist->id)
            ->assertForbidden();
    }

    public function test_finalize_requires_complete_checklist_assessment(): void
    {
        [$audit, , $auditor] = $this->createChecklistDataset('SI', AmiAuditorRole::Lead);

        $this->actingAs($auditor);

        Livewire::test(AuditWorkspace::class, ['audit' => $audit->id])
            ->call('finalizeAudit')
            ->assertHasErrors(['finalize']);

        $this->assertDatabaseHas(AmiAudit::class, [
            'id' => $audit->id,
            'status' => AmiAuditStatus::Ongoing->value,
        ]);
    }

    private function createChecklistDataset(string $unitCode, AmiAuditorRole $role): array
    {
        $unit = $this->createUnit($unitCode);
        $audit = $this->createAudit($unit);
        $indicator = $this->createIndicator("IKU-{$unitCode}");
        $checklist = AmiChecklist::query()->create([
            'ami_audit_id' => $audit->id,
            'standard_indicator_id' => $indicator->id,
            'assessment_result' => null,
            'auditor_notes' => null,
        ]);
        $auditor = $this->createRoleUser('auditor');

        AmiAuditor::query()->create([
            'ami_audit_id' => $audit->id,
            'user_id' => $auditor->id,
            'role' => $role,
        ]);

        return [$audit, $checklist, $auditor];
    }

    private function createAudit(Unit $unit): AmiAudit
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
            'status' => AmiAuditStatus::Ongoing,
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

    private function createRoleUser(string $roleName): User
    {
        $role = Role::findOrCreate($roleName, 'web');

        return User::factory()
            ->create()
            ->assignRole($role);
    }
}
