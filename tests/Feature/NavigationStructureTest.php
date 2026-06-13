<?php

namespace Tests\Feature;

use App\Filament\Pages\AuditSaya;
use App\Filament\Pages\CapaianIndikatorSaya;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\ManagementReviews;
use App\Filament\Pages\ReportsPage;
use App\Filament\Pages\RiwayatRevisiStandar;
use App\Filament\Pages\StandardImprovementProposals;
use App\Filament\Pages\TemuanSaya;
use App\Filament\Pages\VerifikasiTindakLanjut;
use App\Filament\Resources\AchievementReviews\AchievementReviewResource;
use App\Filament\Resources\AmiChecklists\AmiChecklistResource;
use App\Filament\Resources\AmiFindings\AmiFindingResource;
use App\Filament\Resources\CorrectiveActions\CorrectiveActionResource;
use App\Filament\Resources\IndicatorAchievements\IndicatorAchievementResource;
use App\Filament\Resources\IndicatorUnitAssignments\IndicatorUnitAssignmentResource;
use App\Filament\Resources\QualityStandards\QualityStandardResource;
use App\Filament\Resources\StandardCategories\StandardCategoryResource;
use App\Filament\Resources\StandardIndicators\StandardIndicatorResource;
use App\Models\Unit;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NavigationStructureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_unit_pic_navigation_is_workflow_focused(): void
    {
        $user = $this->roleUser('unit_pic', [
            'dashboard.view',
            'corrective-actions.view',
            'quality-documents.view',
            'reports.view',
        ]);

        $this->actingAs($user);

        $this->assertTrue(Dashboard::canAccess());
        $this->assertTrue(CapaianIndikatorSaya::canAccess());
        $this->assertTrue(TemuanSaya::canAccess());
        $this->assertTrue(ReportsPage::canAccess());
        $this->assertFalse(AuditSaya::canAccess());
        $this->assertFalse(ManagementReviews::canAccess());
        $this->assertFalse(StandardImprovementProposals::canAccess());
    }

    public function test_auditor_navigation_is_workflow_focused(): void
    {
        $user = $this->roleUser('auditor', [
            'dashboard.view',
            'corrective-actions.review',
            'reports.view',
        ]);

        $this->actingAs($user);

        $this->assertTrue(Dashboard::canAccess());
        $this->assertTrue(AuditSaya::canAccess());
        $this->assertTrue(VerifikasiTindakLanjut::canAccess());
        $this->assertTrue(ReportsPage::canAccess());
        $this->assertFalse(CapaianIndikatorSaya::canAccess());
        $this->assertFalse(TemuanSaya::canAccess());
        $this->assertFalse(ManagementReviews::canAccess());
    }

    public function test_pimpinan_navigation_is_limited_to_oversight_workflows(): void
    {
        $user = $this->roleUser('pimpinan', [
            'dashboard.view',
            'reports.view',
        ]);

        $this->actingAs($user);

        $this->assertTrue(Dashboard::canAccess());
        $this->assertTrue(ReportsPage::canAccess());
        $this->assertTrue(ManagementReviews::canAccess());
        $this->assertTrue(StandardImprovementProposals::canAccess());
        $this->assertTrue(RiwayatRevisiStandar::canAccess());
        $this->assertFalse(CapaianIndikatorSaya::canAccess());
        $this->assertFalse(AuditSaya::canAccess());
    }

    public function test_technical_resources_are_hidden_from_primary_navigation(): void
    {
        $user = $this->roleUser('admin_lpm', [
            'indicator-achievements.view',
            'corrective-actions.view',
        ]);

        $this->actingAs($user);

        $this->assertFalse(StandardIndicatorResource::shouldRegisterNavigation());
        $this->assertFalse(IndicatorUnitAssignmentResource::shouldRegisterNavigation());
        $this->assertFalse(AmiChecklistResource::shouldRegisterNavigation());
        $this->assertFalse(CorrectiveActionResource::shouldRegisterNavigation());
        $this->assertFalse(AchievementReviewResource::shouldRegisterNavigation());
    }

    public function test_admin_lpm_sees_workflow_and_master_data_navigation(): void
    {
        $user = $this->roleUser('admin_lpm', [
            'indicator-achievements.view',
            'reports.view',
        ]);

        $this->actingAs($user);

        $this->assertTrue(QualityStandardResource::shouldRegisterNavigation());
        $this->assertTrue(IndicatorAchievementResource::shouldRegisterNavigation());
        $this->assertTrue(AmiFindingResource::shouldRegisterNavigation());
        $this->assertTrue(StandardCategoryResource::shouldRegisterNavigation());
        $this->assertTrue(ManagementReviews::canAccess());
        $this->assertTrue(RiwayatRevisiStandar::canAccess());
    }

    #[DataProvider('navigationGroups')]
    public function test_navigation_groups_follow_workflow_structure(string $class, string $expectedGroup): void
    {
        $this->assertSame($expectedGroup, $this->navigationGroup($class));
    }

    public function test_revision_history_page_renders(): void
    {
        $user = $this->roleUser('admin_lpm');

        $this->actingAs($user);

        Livewire::test(RiwayatRevisiStandar::class)
            ->assertSee('Riwayat Revisi Standar')
            ->assertSee('Total Riwayat');
    }

    /**
     * @return array<string, array{0: class-string, 1: string}>
     */
    public static function navigationGroups(): array
    {
        return [
            'quality standards' => [QualityStandardResource::class, 'Penetapan'],
            'indicator monitoring' => [IndicatorAchievementResource::class, 'Pelaksanaan'],
            'audit saya' => [AuditSaya::class, 'Evaluasi AMI'],
            'findings control' => [TemuanSaya::class, 'Pengendalian'],
            'management reviews' => [ManagementReviews::class, 'Peningkatan'],
            'reports' => [ReportsPage::class, 'Laporan'],
        ];
    }

    /**
     * @param  class-string  $class
     */
    private function navigationGroup(string $class): ?string
    {
        $property = (new ReflectionClass($class))->getProperty('navigationGroup');
        $property->setAccessible(true);

        $value = $property->getValue();

        return is_string($value) ? $value : null;
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

        $unitId = null;

        if ($roleName === 'unit_pic') {
            $unitId = Unit::query()->create([
                'code' => 'UPPS',
                'name' => 'Unit Pengelola Program Studi',
                'type' => null,
                'is_active' => true,
            ])->id;
        }

        return User::factory()
            ->create([
                'unit_id' => $unitId,
            ])
            ->assignRole($role);
    }
}
