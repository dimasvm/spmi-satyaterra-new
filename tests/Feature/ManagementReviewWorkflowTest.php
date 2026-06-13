<?php

namespace Tests\Feature;

use App\Enums\QualityStandardStatus;
use App\Enums\StandardImprovementProposalStatus;
use App\Enums\StandardImprovementProposalType;
use App\Enums\StandardIndicatorType;
use App\Enums\StandardRevisionType;
use App\Filament\Pages\ViewManagementReview;
use App\Filament\Pages\ViewStandardImprovementProposal;
use App\Models\ManagementReview;
use App\Models\QualityStandard;
use App\Models\SpmiPeriod;
use App\Models\StandardCategory;
use App\Models\StandardImprovementProposal;
use App\Models\StandardIndicator;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ManagementReviewWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    #[Test]
    public function approved_target_revision_updates_indicator_and_records_history(): void
    {
        $user = User::factory()->create();
        $period = SpmiPeriod::query()->create([
            'name' => 'SPMI 2026',
            'academic_year' => '2026/2027',
            'semester' => 'ganjil',
            'start_date' => '2026-07-01',
            'end_date' => '2026-12-31',
            'status' => 'active',
        ]);
        $category = StandardCategory::query()->create([
            'code' => 'STD',
            'name' => 'Standar Pendidikan',
        ]);
        $standard = QualityStandard::query()->create([
            'standard_category_id' => $category->id,
            'spmi_period_id' => $period->id,
            'code' => 'STD-01',
            'name' => 'Standar Pendidikan',
            'description' => 'Deskripsi standar lama',
            'status' => QualityStandardStatus::Active,
            'version' => 1,
        ]);
        $indicator = StandardIndicator::query()->create([
            'quality_standard_id' => $standard->id,
            'code' => 'IKU-01',
            'statement' => 'Indikator lama',
            'indicator_type' => StandardIndicatorType::Percentage,
            'target_operator' => '>=',
            'target_value' => 75,
            'target_unit' => '%',
        ]);

        $proposal = StandardImprovementProposal::query()->create([
            'quality_standard_id' => $standard->id,
            'standard_indicator_id' => $indicator->id,
            'proposal_type' => StandardImprovementProposalType::ReviseTarget,
            'title' => 'Naikkan target indikator',
            'proposed_change' => 'Target dinaikkan karena capaian sudah stabil.',
            'proposed_target_operator' => '>=',
            'proposed_target_value' => 85,
            'proposed_target_unit' => '%',
            'target_spmi_period_id' => $period->id,
            'status' => StandardImprovementProposalStatus::Approved,
        ]);

        $proposal->implement($user);

        $indicator->refresh();
        $proposal->refresh();

        $this->assertSame('85.00', $indicator->target_value);
        $this->assertSame('>=', $indicator->target_operator->value);
        $this->assertSame('%', $indicator->target_unit);
        $this->assertSame(StandardImprovementProposalStatus::Implemented, $proposal->status);
        $this->assertSame($user->id, $proposal->implemented_by);

        $this->assertDatabaseHas('standard_revision_histories', [
            'standard_improvement_proposal_id' => $proposal->id,
            'standard_indicator_id' => $indicator->id,
            'revision_type' => StandardRevisionType::TargetRevision->value,
            'revised_by' => $user->id,
        ]);
    }

    #[Test]
    public function management_review_detail_uses_decision_workspace_route(): void
    {
        $user = $this->roleUser('admin_lpm');
        $period = $this->spmiPeriod();
        $review = ManagementReview::query()->create([
            'spmi_period_id' => $period->id,
            'title' => 'RTM Akademik 2026',
            'summary' => 'Ringkasan hasil AMI.',
            'conclusion' => 'Perlu peningkatan standar.',
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user);

        Livewire::test(ViewManagementReview::class, ['managementReview' => $review->id])
            ->assertSee('Ringkasan Hasil AMI')
            ->assertSee('Keputusan RTM')
            ->assertSee('Usulan Peningkatan');
    }

    #[Test]
    public function proposal_detail_supports_approval_and_implementation_actions(): void
    {
        $admin = $this->roleUser('admin_lpm');
        $leader = $this->roleUser('pimpinan');
        $period = $this->spmiPeriod();
        $category = StandardCategory::query()->create([
            'code' => 'STD',
            'name' => 'Standar Pendidikan',
        ]);
        $standard = QualityStandard::query()->create([
            'standard_category_id' => $category->id,
            'spmi_period_id' => $period->id,
            'code' => 'STD-01',
            'name' => 'Standar Pendidikan',
            'description' => 'Deskripsi lama',
            'status' => QualityStandardStatus::Active,
            'version' => 1,
        ]);

        $proposal = StandardImprovementProposal::query()->create([
            'quality_standard_id' => $standard->id,
            'proposal_type' => StandardImprovementProposalType::ReviseStandard,
            'title' => 'Revisi Standar Pendidikan',
            'proposed_change' => 'Deskripsi standar diperbarui.',
            'target_spmi_period_id' => $period->id,
            'status' => StandardImprovementProposalStatus::Submitted,
            'proposed_by' => $admin->id,
        ]);

        $this->actingAs($leader);

        Livewire::test(ViewStandardImprovementProposal::class, ['proposal' => $proposal->id])
            ->callAction('approve', ['review_notes' => 'Disetujui untuk implementasi.'])
            ->assertNotified();

        $this->actingAs($admin);

        Livewire::test(ViewStandardImprovementProposal::class, ['proposal' => $proposal->id])
            ->assertSee('Objek Terkait')
            ->callAction('implement')
            ->assertNotified();

        $proposal->refresh();
        $standard->refresh();

        $this->assertSame(StandardImprovementProposalStatus::Implemented, $proposal->status);
        $this->assertSame(QualityStandardStatus::Revised, $standard->status);
        $this->assertSame(2, $standard->version);
        $this->assertDatabaseHas('standard_revision_histories', [
            'standard_improvement_proposal_id' => $proposal->id,
            'quality_standard_id' => $standard->id,
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
            'status' => 'active',
        ]);
    }

    private function roleUser(string $roleName): User
    {
        $role = Role::findOrCreate($roleName, 'web');

        return User::factory()
            ->create()
            ->assignRole($role);
    }
}
