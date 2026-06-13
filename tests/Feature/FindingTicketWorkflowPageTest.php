<?php

namespace Tests\Feature;

use App\Enums\AmiAuditorRole;
use App\Enums\AmiAuditStatus;
use App\Enums\AmiFindingCategory;
use App\Enums\AmiFindingStatus;
use App\Enums\AmiPeriodStatus;
use App\Enums\CorrectiveActionReviewStatus;
use App\Enums\CorrectiveActionStatus;
use App\Enums\QualityStandardStatus;
use App\Enums\SpmiPeriodStatus;
use App\Enums\StandardIndicatorType;
use App\Enums\TargetOperator;
use App\Filament\Pages\MonitoringTemuan;
use App\Filament\Pages\TemuanSaya;
use App\Filament\Pages\VerifikasiTindakLanjut;
use App\Models\AmiAudit;
use App\Models\AmiAuditor;
use App\Models\AmiFinding;
use App\Models\AmiPeriod;
use App\Models\CorrectiveAction;
use App\Models\CorrectiveActionEvidence;
use App\Models\CorrectiveActionReview;
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

class FindingTicketWorkflowPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_unit_pic_can_submit_corrective_action_from_temuan_saya(): void
    {
        [$finding] = $this->createFindingDataset('SI');
        $unitUser = $this->createRoleUser('unit_pic', $finding->audit->auditee_unit_id, [
            'corrective-actions.view',
            'corrective-actions.create',
            'corrective-actions.update',
            'corrective-actions.submit',
        ]);

        $this->actingAs($unitUser);

        Livewire::test(TemuanSaya::class)
            ->assertSee($finding->finding_number)
            ->call('openTicket', $finding->id)
            ->set('rootCauseAnalysis', 'Dokumen belum terkonsolidasi.')
            ->set('actionPlan', 'Melengkapi dan mengesahkan dokumen.')
            ->set('picUserId', $unitUser->id)
            ->set('targetDate', '2026-08-15')
            ->set('externalUrl', 'https://example.com/bukti-perbaikan')
            ->set('evidenceDescription', 'Bukti perbaikan.')
            ->call('submitVerification')
            ->assertNotified();

        $this->assertDatabaseHas(CorrectiveAction::class, [
            'ami_finding_id' => $finding->id,
            'root_cause_analysis' => 'Dokumen belum terkonsolidasi.',
            'action_plan' => 'Melengkapi dan mengesahkan dokumen.',
            'pic_user_id' => $unitUser->id,
            'target_date' => '2026-08-15 00:00:00',
            'status' => CorrectiveActionStatus::Submitted->value,
            'submitted_by' => $unitUser->id,
        ]);
        $this->assertDatabaseHas(CorrectiveActionEvidence::class, [
            'external_url' => 'https://example.com/bukti-perbaikan',
            'description' => 'Bukti perbaikan.',
            'uploaded_by' => $unitUser->id,
        ]);
        $this->assertDatabaseHas(AmiFinding::class, [
            'id' => $finding->id,
            'status' => AmiFindingStatus::WaitingVerification->value,
        ]);
    }

    public function test_monitoring_temuan_filters_by_unit_and_status(): void
    {
        [$visibleFinding] = $this->createFindingDataset('SI');
        [$otherFinding] = $this->createFindingDataset('AK');
        $admin = $this->createRoleUser('admin_lpm');

        $this->actingAs($admin);

        Livewire::test(MonitoringTemuan::class)
            ->set('selectedUnitId', $visibleFinding->audit->auditee_unit_id)
            ->set('selectedStatus', AmiFindingStatus::Open->value)
            ->assertSee($visibleFinding->finding_number)
            ->assertDontSee($otherFinding->finding_number);
    }

    public function test_auditor_can_accept_submitted_corrective_action(): void
    {
        [$finding, $auditor] = $this->createFindingDataset('SI');
        $unitUser = $this->createRoleUser('unit_pic', $finding->audit->auditee_unit_id);
        $this->givePermissions($auditor, [
            'corrective-actions.view',
            'corrective-actions.review',
        ]);
        $correctiveAction = $this->createCorrectiveAction($finding, $unitUser);

        $this->actingAs($auditor);

        Livewire::test(VerifikasiTindakLanjut::class)
            ->assertSee($finding->finding_number)
            ->call('openDetail', $correctiveAction->id)
            ->call('accept')
            ->assertNotified();

        $this->assertDatabaseHas(CorrectiveAction::class, [
            'id' => $correctiveAction->id,
            'status' => CorrectiveActionStatus::Accepted->value,
        ]);
        $this->assertDatabaseHas(AmiFinding::class, [
            'id' => $finding->id,
            'status' => AmiFindingStatus::Closed->value,
        ]);
        $this->assertDatabaseHas(CorrectiveActionReview::class, [
            'corrective_action_id' => $correctiveAction->id,
            'reviewer_id' => $auditor->id,
            'status' => CorrectiveActionReviewStatus::Accepted->value,
        ]);
    }

    public function test_revision_request_requires_notes(): void
    {
        [$finding, $auditor] = $this->createFindingDataset('SI');
        $unitUser = $this->createRoleUser('unit_pic', $finding->audit->auditee_unit_id);
        $this->givePermissions($auditor, [
            'corrective-actions.view',
            'corrective-actions.review',
        ]);
        $correctiveAction = $this->createCorrectiveAction($finding, $unitUser);

        $this->actingAs($auditor);

        Livewire::test(VerifikasiTindakLanjut::class)
            ->call('openDetail', $correctiveAction->id)
            ->call('requestRevision')
            ->assertHasErrors(['reviewNotes'])
            ->set('reviewNotes', 'Bukti belum cukup.')
            ->call('requestRevision')
            ->assertNotified();

        $this->assertDatabaseHas(CorrectiveAction::class, [
            'id' => $correctiveAction->id,
            'status' => CorrectiveActionStatus::NeedRevision->value,
        ]);
        $this->assertDatabaseHas(AmiFinding::class, [
            'id' => $finding->id,
            'status' => AmiFindingStatus::NeedRevision->value,
        ]);
    }

    private function createFindingDataset(string $unitCode): array
    {
        $unit = $this->createUnit($unitCode);
        $audit = $this->createAudit($unit);
        $indicator = $this->createIndicator("IKU-{$unitCode}");
        $auditor = $this->createRoleUser('auditor');

        AmiAuditor::query()->create([
            'ami_audit_id' => $audit->id,
            'user_id' => $auditor->id,
            'role' => AmiAuditorRole::Lead,
        ]);

        $finding = AmiFinding::query()->create([
            'ami_audit_id' => $audit->id,
            'standard_indicator_id' => $indicator->id,
            'category' => AmiFindingCategory::Major,
            'description' => 'Temuan mayor '.$unitCode,
            'recommendation' => 'Perlu tindak lanjut.',
            'due_date' => '2026-07-30',
            'status' => AmiFindingStatus::Open,
        ]);

        return [$finding, $auditor];
    }

    private function createCorrectiveAction(AmiFinding $finding, User $pic): CorrectiveAction
    {
        $correctiveAction = CorrectiveAction::query()->create([
            'ami_finding_id' => $finding->id,
            'root_cause_analysis' => 'Akar masalah.',
            'action_plan' => 'Rencana perbaikan.',
            'pic_user_id' => $pic->id,
            'target_date' => '2026-08-15',
            'status' => CorrectiveActionStatus::Submitted,
            'submitted_at' => now(),
            'submitted_by' => $pic->id,
        ]);

        CorrectiveActionEvidence::query()->create([
            'corrective_action_id' => $correctiveAction->id,
            'external_url' => 'https://example.com/bukti',
            'uploaded_by' => $pic->id,
        ]);

        $finding->update(['status' => AmiFindingStatus::WaitingVerification]);

        return $correctiveAction;
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
            'status' => AmiAuditStatus::Finalized,
            'finalized_at' => now(),
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

    /**
     * @param  array<int, string>  $permissions
     */
    private function createRoleUser(string $roleName, ?int $unitId = null, array $permissions = []): User
    {
        $role = Role::findOrCreate($roleName, 'web');

        if ($permissions !== []) {
            $role->syncPermissions(collect($permissions)->map(fn (string $permission): Permission => Permission::findOrCreate($permission, 'web')));
        }

        return User::factory()
            ->create(['unit_id' => $unitId])
            ->assignRole($role);
    }

    /**
     * @param  array<int, string>  $permissions
     */
    private function givePermissions(User $user, array $permissions): void
    {
        $user->givePermissionTo(collect($permissions)->map(fn (string $permission): Permission => Permission::findOrCreate($permission, 'web')));
    }
}
