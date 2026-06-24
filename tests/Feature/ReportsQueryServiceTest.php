<?php

namespace Tests\Feature;

use App\Enums\AmiAuditorRole;
use App\Enums\AmiAuditStatus;
use App\Enums\AmiPeriodStatus;
use App\Enums\IndicatorAssignmentStatus;
use App\Enums\QualityStandardStatus;
use App\Enums\ReportType;
use App\Enums\SpmiPeriodStatus;
use App\Enums\StandardIndicatorType;
use App\Enums\SubmissionStatus;
use App\Enums\TargetOperator;
use App\Models\AmiAudit;
use App\Models\AmiAuditor;
use App\Models\AmiPeriod;
use App\Models\IndicatorAchievement;
use App\Models\IndicatorUnitAssignment;
use App\Models\QualityStandard;
use App\Models\SpmiPeriod;
use App\Models\StandardCategory;
use App\Models\StandardIndicator;
use App\Models\Unit;
use App\Models\User;
use App\Services\Reports\ReportQueryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReportsQueryServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_unit_pic_only_receives_own_indicator_achievement_rows(): void
    {
        $period = $this->createSpmiPeriod();
        $indicator = $this->createIndicator('IKU-001');
        $ownUnit = $this->createUnit('SI');
        $otherUnit = $this->createUnit('AK');
        $ownUser = $this->createRoleUser('unit_pic', $ownUnit->id);

        $this->createAchievement($period, $indicator, $ownUnit, 87);
        $this->createAchievement($period, $indicator, $otherUnit, 91);

        $this->actingAs($ownUser);

        $rows = app(ReportQueryService::class)->rows(ReportType::IndicatorByUnit, []);

        $this->assertCount(1, $rows);
        $this->assertSame('Unit SI', $rows->first()['unit']);
    }

    public function test_parent_standard_category_filter_includes_child_subcategory_standards(): void
    {
        $period = $this->createSpmiPeriod();
        $parentCategory = StandardCategory::query()->create([
            'code' => 'PDD',
            'name' => 'Pendidikan',
            'description' => null,
        ]);
        $subcategory = StandardCategory::query()->create([
            'parent_id' => $parentCategory->id,
            'code' => 'PDD-LRN',
            'name' => 'Luaran',
            'description' => null,
        ]);
        $indicator = $this->createIndicator('IKU-PDD', $subcategory);
        $unit = $this->createUnit('TI');

        $this->createAchievement($period, $indicator, $unit, 88);
        $this->actingAs($this->createRoleUser('admin_lpm'));

        $rows = app(ReportQueryService::class)->rows(ReportType::IndicatorByPeriod, [
            'standard_category_id' => $parentCategory->id,
        ]);

        $this->assertCount(1, $rows);
        $this->assertSame('Standar Mutu IKU-PDD', $rows->first()['standar']);
    }

    public function test_auditor_only_receives_assigned_ami_audit_rows(): void
    {
        $period = $this->createSpmiPeriod();
        $amiPeriod = $this->createAmiPeriod($period);
        $auditor = $this->createRoleUser('auditor');
        $assignedAudit = $this->createAmiAudit($amiPeriod, $this->createUnit('TI'));
        $unassignedAudit = $this->createAmiAudit($amiPeriod, $this->createUnit('DKV'));

        AmiAuditor::query()->create([
            'ami_audit_id' => $assignedAudit->id,
            'user_id' => $auditor->id,
            'role' => AmiAuditorRole::Lead,
        ]);

        $this->actingAs($auditor);

        $rows = app(ReportQueryService::class)->rows(ReportType::AmiByPeriod, []);

        $this->assertCount(1, $rows);
        $this->assertSame('Unit TI', $rows->first()['unit_auditee']);
        $this->assertNotSame($unassignedAudit->auditeeUnit->name, $rows->first()['unit_auditee']);
    }

    private function createAchievement(
        SpmiPeriod $period,
        StandardIndicator $indicator,
        Unit $unit,
        int $realizationValue,
    ): IndicatorAchievement {
        $assignment = IndicatorUnitAssignment::query()->create([
            'standard_indicator_id' => $indicator->id,
            'unit_id' => $unit->id,
            'spmi_period_id' => $period->id,
            'due_date' => '2026-12-31',
            'status' => IndicatorAssignmentStatus::Assigned,
        ]);

        return IndicatorAchievement::query()->create([
            'assignment_id' => $assignment->id,
            'realization_value' => $realizationValue,
            'submission_status' => SubmissionStatus::Submitted,
        ]);
    }

    private function createAmiAudit(AmiPeriod $period, Unit $unit): AmiAudit
    {
        return AmiAudit::query()->create([
            'ami_period_id' => $period->id,
            'auditee_unit_id' => $unit->id,
            'scheduled_date' => '2026-06-01',
            'status' => AmiAuditStatus::Ongoing,
        ]);
    }

    private function createAmiPeriod(SpmiPeriod $period): AmiPeriod
    {
        return AmiPeriod::query()->create([
            'spmi_period_id' => $period->id,
            'name' => 'AMI 2026',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-30',
            'status' => AmiPeriodStatus::Ongoing,
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

    private function createIndicator(string $code, ?StandardCategory $category = null): StandardIndicator
    {
        $category ??= StandardCategory::query()->firstOrCreate(
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
