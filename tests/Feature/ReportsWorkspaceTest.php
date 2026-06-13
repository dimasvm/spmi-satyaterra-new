<?php

namespace Tests\Feature;

use App\Enums\AmiAuditorRole;
use App\Enums\AmiAuditStatus;
use App\Enums\AmiPeriodStatus;
use App\Enums\QualityStandardStatus;
use App\Enums\ReportType;
use App\Enums\SpmiPeriodStatus;
use App\Enums\StandardImprovementProposalStatus;
use App\Enums\StandardImprovementProposalType;
use App\Filament\Pages\ReportsPage;
use App\Models\AmiAudit;
use App\Models\AmiAuditor;
use App\Models\AmiPeriod;
use App\Models\ManagementReview;
use App\Models\QualityStandard;
use App\Models\SpmiPeriod;
use App\Models\StandardCategory;
use App\Models\StandardImprovementProposal;
use App\Models\Unit;
use App\Models\User;
use App\Services\Reports\ReportQueryService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReportsWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_admin_lpm_can_render_reports_workspace_cards(): void
    {
        $user = $this->roleUser('admin_lpm', permissions: ['reports.view', 'reports.export']);

        $this->actingAs($user);

        Livewire::test(ReportsPage::class)
            ->assertSee('Pusat Laporan')
            ->assertSee('Unduh dan pantau laporan SPMI berdasarkan periode, unit, dan siklus mutu.')
            ->assertSee('Laporan Capaian Indikator')
            ->assertSee('Laporan RTM')
            ->assertSee('Laporan Peningkatan Standar')
            ->assertSee('Generate PDF')
            ->assertSee('Export Excel');
    }

    public function test_reports_workspace_previews_management_review_and_standard_improvement_reports(): void
    {
        $user = $this->roleUser('admin_lpm', permissions: ['reports.view']);
        $period = $this->spmiPeriod();
        $amiPeriod = $this->amiPeriod($period);
        $review = ManagementReview::query()->create([
            'spmi_period_id' => $period->id,
            'ami_period_id' => $amiPeriod->id,
            'title' => 'RTM Akademik 2026',
            'meeting_date' => '2026-06-20',
            'location' => 'Ruang Senat',
            'status' => 'completed',
            'created_by' => $user->id,
        ]);
        $standard = $this->qualityStandard($period);

        StandardImprovementProposal::query()->create([
            'management_review_id' => $review->id,
            'quality_standard_id' => $standard->id,
            'proposal_type' => StandardImprovementProposalType::ReviseStandard,
            'title' => 'Revisi Standar Pembelajaran',
            'proposed_change' => 'Perbarui kriteria pembelajaran.',
            'target_spmi_period_id' => $period->id,
            'status' => StandardImprovementProposalStatus::Submitted,
            'proposed_by' => $user->id,
        ]);

        $this->actingAs($user);

        Livewire::test(ReportsPage::class)
            ->call('selectReport', ReportType::ManagementReviews->value)
            ->assertSee('RTM Akademik 2026')
            ->assertSet('headings.0', 'Periode SPMI')
            ->call('selectReport', ReportType::StandardImprovements->value)
            ->assertSee('Revisi Standar Pembelajaran')
            ->assertSet('headings.0', 'Periode Target');
    }

    public function test_auditor_only_receives_assigned_management_review_and_improvement_rows(): void
    {
        $auditor = $this->roleUser('auditor');
        $period = $this->spmiPeriod();
        $assignedAmiPeriod = $this->amiPeriod($period, 'AMI Assigned');
        $unassignedAmiPeriod = $this->amiPeriod($period, 'AMI Unassigned');
        $assignedAudit = $this->amiAudit($assignedAmiPeriod, $this->unit('TI'));
        $this->amiAudit($unassignedAmiPeriod, $this->unit('DKV'));
        $assignedReview = $this->managementReview($period, $assignedAmiPeriod, 'RTM Assigned');
        $unassignedReview = $this->managementReview($period, $unassignedAmiPeriod, 'RTM Unassigned');
        $standard = $this->qualityStandard($period);

        AmiAuditor::query()->create([
            'ami_audit_id' => $assignedAudit->id,
            'user_id' => $auditor->id,
            'role' => AmiAuditorRole::Lead,
        ]);

        StandardImprovementProposal::query()->create([
            'management_review_id' => $assignedReview->id,
            'quality_standard_id' => $standard->id,
            'proposal_type' => StandardImprovementProposalType::ReviseStandard,
            'title' => 'Assigned Improvement',
            'proposed_change' => 'Perbaikan audit ditugaskan.',
            'target_spmi_period_id' => $period->id,
            'status' => StandardImprovementProposalStatus::Submitted,
            'proposed_by' => $auditor->id,
        ]);
        StandardImprovementProposal::query()->create([
            'management_review_id' => $unassignedReview->id,
            'quality_standard_id' => $standard->id,
            'proposal_type' => StandardImprovementProposalType::ReviseStandard,
            'title' => 'Unassigned Improvement',
            'proposed_change' => 'Perbaikan audit lain.',
            'target_spmi_period_id' => $period->id,
            'status' => StandardImprovementProposalStatus::Submitted,
            'proposed_by' => $auditor->id,
        ]);

        $this->actingAs($auditor);

        $reports = app(ReportQueryService::class);
        $reviewRows = $reports->rows(ReportType::ManagementReviews, []);
        $proposalRows = $reports->rows(ReportType::StandardImprovements, []);

        $this->assertCount(1, $reviewRows);
        $this->assertSame('RTM Assigned', $reviewRows->first()['judul_rtm']);
        $this->assertCount(1, $proposalRows);
        $this->assertSame('Assigned Improvement', $proposalRows->first()['judul_usulan']);
    }

    private function managementReview(SpmiPeriod $period, AmiPeriod $amiPeriod, string $title): ManagementReview
    {
        return ManagementReview::query()->create([
            'spmi_period_id' => $period->id,
            'ami_period_id' => $amiPeriod->id,
            'title' => $title,
            'meeting_date' => '2026-06-20',
            'status' => 'completed',
        ]);
    }

    private function amiAudit(AmiPeriod $period, Unit $unit): AmiAudit
    {
        return AmiAudit::query()->create([
            'ami_period_id' => $period->id,
            'auditee_unit_id' => $unit->id,
            'scheduled_date' => '2026-06-10',
            'status' => AmiAuditStatus::Ongoing,
        ]);
    }

    private function amiPeriod(SpmiPeriod $period, string $name = 'AMI 2026'): AmiPeriod
    {
        return AmiPeriod::query()->create([
            'spmi_period_id' => $period->id,
            'name' => $name,
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-30',
            'status' => AmiPeriodStatus::Ongoing,
        ]);
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

    private function qualityStandard(SpmiPeriod $period): QualityStandard
    {
        $category = StandardCategory::query()->create([
            'code' => 'STD',
            'name' => 'Standar Pendidikan',
        ]);

        return QualityStandard::query()->create([
            'standard_category_id' => $category->id,
            'spmi_period_id' => $period->id,
            'code' => 'STD-01',
            'name' => 'Standar Pembelajaran',
            'description' => 'Standar pembelajaran.',
            'status' => QualityStandardStatus::Active,
            'version' => 1,
        ]);
    }

    private function unit(string $code): Unit
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
    private function roleUser(string $roleName, array $permissions = []): User
    {
        $role = Role::findOrCreate($roleName, 'web');

        foreach ($permissions as $permission) {
            $role->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }

        return User::factory()
            ->create()
            ->assignRole($role);
    }
}
