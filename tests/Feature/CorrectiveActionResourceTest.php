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
use App\Filament\Resources\CorrectiveActions\Pages\ListCorrectiveActions;
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
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CorrectiveActionResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_unit_pic_can_submit_corrective_action_with_evidence(): void
    {
        [$finding, $auditor] = $this->createFindingDataset('SI');
        $unitUser = $this->createRoleUser('unit_pic', $finding->audit->auditee_unit_id, [
            'corrective-actions.view',
            'corrective-actions.update',
            'corrective-actions.submit',
        ]);
        $correctiveAction = $this->createCorrectiveAction($finding, $unitUser);

        CorrectiveActionEvidence::query()->create([
            'corrective_action_id' => $correctiveAction->id,
            'file_name' => 'bukti.pdf',
            'file_path' => 'corrective-action-evidences/bukti.pdf',
            'uploaded_by' => $unitUser->id,
        ]);

        $this->actingAs($unitUser);

        Livewire::test(ListCorrectiveActions::class)
            ->callAction(TestAction::make('submitVerification')->table($correctiveAction))
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas(CorrectiveAction::class, [
            'id' => $correctiveAction->id,
            'status' => CorrectiveActionStatus::Submitted->value,
            'submitted_by' => $unitUser->id,
        ]);
        $this->assertDatabaseHas(AmiFinding::class, [
            'id' => $finding->id,
            'status' => AmiFindingStatus::WaitingVerification->value,
        ]);
        $this->assertNotNull($auditor);
    }

    public function test_minor_or_major_finding_requires_evidence_before_submit(): void
    {
        [$finding] = $this->createFindingDataset('AK');
        $unitUser = $this->createRoleUser('unit_pic', $finding->audit->auditee_unit_id, [
            'corrective-actions.view',
            'corrective-actions.update',
            'corrective-actions.submit',
        ]);
        $correctiveAction = $this->createCorrectiveAction($finding, $unitUser);

        $this->actingAs($unitUser);

        Livewire::test(ListCorrectiveActions::class)
            ->callAction(TestAction::make('submitVerification')->table($correctiveAction));

        $this->assertDatabaseHas(CorrectiveAction::class, [
            'id' => $correctiveAction->id,
            'status' => CorrectiveActionStatus::Draft->value,
        ]);
    }

    public function test_assigned_auditor_can_accept_submitted_corrective_action(): void
    {
        [$finding, $auditor] = $this->createFindingDataset('MK');
        $unitUser = $this->createRoleUser('unit_pic', $finding->audit->auditee_unit_id);
        $this->givePermissions($auditor, [
            'corrective-actions.view',
            'corrective-actions.review',
        ]);
        $correctiveAction = $this->createCorrectiveAction($finding, $unitUser, CorrectiveActionStatus::Submitted);

        $this->actingAs($auditor);

        Livewire::test(ListCorrectiveActions::class)
            ->callAction(TestAction::make('accept')->table($correctiveAction))
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas(CorrectiveActionReview::class, [
            'corrective_action_id' => $correctiveAction->id,
            'reviewer_id' => $auditor->id,
            'status' => CorrectiveActionReviewStatus::Accepted->value,
        ]);
        $this->assertDatabaseHas(CorrectiveAction::class, [
            'id' => $correctiveAction->id,
            'status' => CorrectiveActionStatus::Accepted->value,
        ]);
        $this->assertDatabaseHas(AmiFinding::class, [
            'id' => $finding->id,
            'status' => AmiFindingStatus::Closed->value,
        ]);
    }

    public function test_unit_pic_only_sees_corrective_actions_for_own_unit(): void
    {
        [$ownFinding] = $this->createFindingDataset('TI');
        [$otherFinding] = $this->createFindingDataset('DKV');
        $unitUser = $this->createRoleUser('unit_pic', $ownFinding->audit->auditee_unit_id, [
            'corrective-actions.view',
        ]);
        $ownAction = $this->createCorrectiveAction($ownFinding, $unitUser);
        $otherAction = $this->createCorrectiveAction($otherFinding, $this->createRoleUser('unit_pic', $otherFinding->audit->auditee_unit_id));

        $this->actingAs($unitUser);

        Livewire::test(ListCorrectiveActions::class)
            ->assertCanSeeTableRecords([$ownAction])
            ->assertCanNotSeeTableRecords([$otherAction]);
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

    private function createCorrectiveAction(
        AmiFinding $finding,
        User $pic,
        CorrectiveActionStatus $status = CorrectiveActionStatus::Draft,
    ): CorrectiveAction {
        return CorrectiveAction::query()->create([
            'ami_finding_id' => $finding->id,
            'root_cause_analysis' => 'Akar masalah sudah dianalisis.',
            'action_plan' => 'Menyusun dan melaksanakan rencana perbaikan.',
            'pic_user_id' => $pic->id,
            'target_date' => '2026-08-15',
            'status' => $status,
            'submitted_at' => $status === CorrectiveActionStatus::Submitted ? now() : null,
            'submitted_by' => $status === CorrectiveActionStatus::Submitted ? $pic->id : null,
        ]);
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

        $this->givePermissions($role, $permissions);

        return User::factory()
            ->create([
                'unit_id' => $unitId,
            ])
            ->assignRole($role);
    }

    /**
     * @param  array<int, string>  $permissions
     */
    private function givePermissions(Role|User $permissionable, array $permissions): void
    {
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $permissionable->givePermissionTo($permissions);
    }
}
