<?php

namespace Tests\Feature;

use App\Enums\AmiAuditStatus;
use App\Enums\AmiFindingCategory;
use App\Enums\AmiFindingStatus;
use App\Enums\AmiPeriodStatus;
use App\Enums\IndicatorAssignmentPriority;
use App\Enums\IndicatorAssignmentStatus;
use App\Enums\QualityStandardStatus;
use App\Enums\SpmiPeriodStatus;
use App\Enums\StandardImprovementProposalStatus;
use App\Enums\StandardImprovementProposalType;
use App\Enums\StandardIndicatorType;
use App\Enums\TargetOperator;
use App\Filament\Pages\AuditSaya;
use App\Filament\Pages\CapaianIndikatorSaya;
use App\Filament\Pages\InboxValidasiCapaian;
use App\Filament\Pages\TemuanSaya;
use App\Filament\Pages\ViewStandardImprovementProposal;
use App\Models\AmiAudit;
use App\Models\AmiAuditor;
use App\Models\AmiFinding;
use App\Models\AmiPeriod;
use App\Models\IndicatorUnitAssignment;
use App\Models\QualityStandard;
use App\Models\SpmiPeriod;
use App\Models\StandardCategory;
use App\Models\StandardImprovementProposal;
use App\Models\StandardIndicator;
use App\Models\Unit;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WorkflowAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_unit_pic_only_sees_own_indicator_assignments(): void
    {
        $period = $this->spmiPeriod();
        $ownedUnit = $this->unit('PSI');
        $otherUnit = $this->unit('FEB');

        $this->assignment($period, $ownedUnit, 'IKU-UNIT-OWN');
        $this->assignment($period, $otherUnit, 'IKU-UNIT-OTHER');

        $this->actingAs($this->roleUser('unit_pic', unit: $ownedUnit));

        Livewire::test(CapaianIndikatorSaya::class)
            ->assertSee('IKU-UNIT-OWN')
            ->assertDontSee('IKU-UNIT-OTHER');
    }

    public function test_unit_pic_cannot_open_lpm_validation_inbox(): void
    {
        $this->actingAs($this->roleUser('unit_pic', unit: $this->unit('PSI')));

        Livewire::test(InboxValidasiCapaian::class)
            ->assertForbidden();
    }

    public function test_admin_lpm_can_open_lpm_validation_inbox(): void
    {
        $this->actingAs($this->roleUser('admin_lpm', [
            'indicator-achievements.review',
        ]));

        Livewire::test(InboxValidasiCapaian::class)
            ->assertSee('Inbox Validasi Capaian');
    }

    public function test_auditor_only_sees_assigned_audits(): void
    {
        $auditor = $this->roleUser('auditor');
        $assignedAudit = $this->audit($this->unit('SI'));
        $otherAudit = $this->audit($this->unit('AK'));

        AmiAuditor::query()->create([
            'ami_audit_id' => $assignedAudit->id,
            'user_id' => $auditor->id,
            'role' => 'lead',
        ]);

        $this->actingAs($auditor);

        Livewire::test(AuditSaya::class)
            ->assertSee($assignedAudit->auditeeUnit->name)
            ->assertDontSee($otherAudit->auditeeUnit->name);
    }

    public function test_unit_pic_only_sees_findings_for_own_finalized_unit(): void
    {
        $ownedUnit = $this->unit('PSI');
        $otherUnit = $this->unit('FEB');
        $ownedFinding = $this->finding($this->audit($ownedUnit, AmiAuditStatus::Finalized), 'Temuan unit sendiri');
        $otherFinding = $this->finding($this->audit($otherUnit, AmiAuditStatus::Finalized), 'Temuan unit lain');

        $this->actingAs($this->roleUser('unit_pic', [
            'corrective-actions.view',
        ], $ownedUnit));

        Livewire::test(TemuanSaya::class)
            ->assertSee($ownedFinding->description)
            ->assertDontSee($otherFinding->description);
    }

    public function test_pimpinan_can_approve_proposal_and_admin_implementation_records_revision_history(): void
    {
        $admin = $this->roleUser('admin_lpm');
        $leader = $this->roleUser('pimpinan');
        $period = $this->spmiPeriod();
        $standard = $this->qualityStandard($period, 'STD-APPROVAL');

        $proposal = StandardImprovementProposal::query()->create([
            'quality_standard_id' => $standard->id,
            'proposal_type' => StandardImprovementProposalType::ReviseStandard,
            'title' => 'Revisi standar hasil RTM',
            'proposed_standard_description' => 'Deskripsi standar setelah RTM.',
            'proposed_change' => 'Perbarui deskripsi standar.',
            'target_spmi_period_id' => $period->id,
            'status' => StandardImprovementProposalStatus::Submitted,
            'proposed_by' => $admin->id,
        ]);

        $this->actingAs($leader);

        Livewire::test(ViewStandardImprovementProposal::class, ['proposal' => $proposal->id])
            ->callAction('approve', ['review_notes' => 'Disetujui pimpinan.'])
            ->assertNotified();

        $proposal->refresh();

        $this->assertSame(StandardImprovementProposalStatus::Approved, $proposal->status);
        $this->assertSame($leader->id, $proposal->reviewed_by);

        $this->actingAs($admin);

        Livewire::test(ViewStandardImprovementProposal::class, ['proposal' => $proposal->id])
            ->callAction('implement')
            ->assertNotified();

        $proposal->refresh();
        $standard->refresh();

        $this->assertSame(StandardImprovementProposalStatus::Implemented, $proposal->status);
        $this->assertSame(QualityStandardStatus::Revised, $standard->status);
        $this->assertDatabaseHas('standard_revision_histories', [
            'standard_improvement_proposal_id' => $proposal->id,
            'quality_standard_id' => $standard->id,
            'revised_by' => $admin->id,
        ]);
    }

    /**
     * @param  array<int, string>  $permissions
     */
    private function roleUser(string $roleName, array $permissions = [], ?Unit $unit = null): User
    {
        $role = Role::findOrCreate($roleName, 'web');

        foreach ($permissions as $permission) {
            $role->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }

        return User::factory()
            ->create(['unit_id' => $unit?->id])
            ->assignRole($role);
    }

    private function spmiPeriod(): SpmiPeriod
    {
        return SpmiPeriod::query()->create([
            'name' => 'SPMI 2026',
            'academic_year' => '2026/2027',
            'semester' => 'ganjil',
            'start_date' => '2026-07-01',
            'end_date' => '2026-12-31',
            'status' => SpmiPeriodStatus::Active,
        ]);
    }

    private function unit(string $code): Unit
    {
        return Unit::query()->create([
            'code' => $code,
            'name' => "Unit {$code}",
            'type' => null,
            'is_active' => true,
        ]);
    }

    private function assignment(SpmiPeriod $period, Unit $unit, string $indicatorCode): IndicatorUnitAssignment
    {
        return IndicatorUnitAssignment::query()->create([
            'standard_indicator_id' => $this->indicator($period, $indicatorCode)->id,
            'unit_id' => $unit->id,
            'spmi_period_id' => $period->id,
            'due_date' => '2026-12-31',
            'status' => IndicatorAssignmentStatus::Assigned,
            'is_primary_pic' => true,
            'priority' => IndicatorAssignmentPriority::Normal,
        ]);
    }

    private function qualityStandard(SpmiPeriod $period, string $code): QualityStandard
    {
        $category = StandardCategory::query()->firstOrCreate(
            ['code' => 'STD'],
            ['name' => 'Standar Pendidikan', 'description' => null],
        );

        return QualityStandard::query()->create([
            'standard_category_id' => $category->id,
            'spmi_period_id' => $period->id,
            'code' => $code,
            'name' => 'Standar Pendidikan',
            'description' => 'Deskripsi standar awal.',
            'status' => QualityStandardStatus::Active,
            'version' => 1,
        ]);
    }

    private function indicator(SpmiPeriod $period, string $code): StandardIndicator
    {
        return StandardIndicator::query()->create([
            'quality_standard_id' => $this->qualityStandard($period, 'STD-'.$code)->id,
            'code' => $code,
            'statement' => "Indikator {$code}",
            'indicator_type' => StandardIndicatorType::Percentage,
            'target_operator' => TargetOperator::GreaterThanOrEqual,
            'target_value' => 80,
            'target_unit' => '%',
            'evidence_required' => true,
        ]);
    }

    private function amiPeriod(): AmiPeriod
    {
        return AmiPeriod::query()->create([
            'spmi_period_id' => $this->spmiPeriod()->id,
            'name' => 'AMI 2026',
            'start_date' => '2026-08-01',
            'end_date' => '2026-09-30',
            'status' => AmiPeriodStatus::Ongoing,
        ]);
    }

    private function audit(Unit $unit, AmiAuditStatus $status = AmiAuditStatus::Ongoing): AmiAudit
    {
        return AmiAudit::query()->create([
            'ami_period_id' => $this->amiPeriod()->id,
            'auditee_unit_id' => $unit->id,
            'scheduled_date' => '2026-08-15',
            'status' => $status,
        ]);
    }

    private function finding(AmiAudit $audit, string $description): AmiFinding
    {
        return AmiFinding::query()->create([
            'ami_audit_id' => $audit->id,
            'standard_indicator_id' => $this->indicator($audit->amiPeriod->spmiPeriod, 'IKU-'.$audit->auditeeUnit->code)->id,
            'category' => AmiFindingCategory::Minor,
            'description' => $description,
            'recommendation' => 'Lengkapi tindak lanjut.',
            'due_date' => '2026-10-01',
            'status' => AmiFindingStatus::Open,
        ]);
    }
}
