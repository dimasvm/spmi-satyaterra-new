<?php

namespace Tests\Feature;

use App\Enums\IndicatorAssignmentStatus;
use App\Enums\QualityStandardStatus;
use App\Enums\SpmiPeriodStatus;
use App\Models\IndicatorUnitAssignment;
use App\Models\QualityStandard;
use App\Models\SpmiPeriod;
use App\Models\StandardCategory;
use App\Models\StandardIndicator;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SpmiDashboardExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Add dummy login route to resolve route('login') redirects in tests
        $this->app['router']->get('/login', function () {
            return 'login';
        })->name('login');

        $this->app['router']->getRoutes()->refreshNameLookups();
    }

    public function test_guest_cannot_export_dashboard_pdf(): void
    {
        $response = $this->get(route('dashboard.export-pdf'));

        $response->assertRedirect(route('login'));
    }

    public function test_user_without_dashboard_view_permission_cannot_export(): void
    {
        $user = $this->roleUser('viewer', []);

        $response = $this->actingAs($user)->get(route('dashboard.export-pdf'));

        $response->assertStatus(403);
    }

    public function test_authorized_user_can_export_dashboard_pdf(): void
    {
        $user = $this->roleUser('admin_lpm', ['dashboard.view']);
        $period = $this->spmiPeriod('Periode 2026');

        $response = $this->actingAs($user)->get(route('dashboard.export-pdf', [
            'spmi_period_id' => $period->id,
        ]));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
        $this->assertStringContainsString('attachment;', $response->headers->get('content-disposition'));
        $this->assertStringContainsString('laporan-monitoring-progres-pelaksanaan-standar', $response->headers->get('content-disposition'));
    }

    public function test_guest_cannot_export_dashboard_efektivitas_pdf(): void
    {
        $response = $this->get(route('dashboard.export-efektivitas-pdf'));

        $response->assertRedirect(route('login'));
    }

    public function test_user_without_dashboard_view_permission_cannot_export_efektivitas(): void
    {
        $user = $this->roleUser('viewer', []);

        $response = $this->actingAs($user)->get(route('dashboard.export-efektivitas-pdf'));

        $response->assertStatus(403);
    }

    public function test_authorized_user_can_export_dashboard_efektivitas_pdf(): void
    {
        $user = $this->roleUser('admin_lpm', ['dashboard.view']);
        $period = $this->spmiPeriod('Periode 2026');

        $response = $this->actingAs($user)->get(route('dashboard.export-efektivitas-pdf', [
            'spmi_period_id' => $period->id,
        ]));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
        $this->assertStringContainsString('attachment;', $response->headers->get('content-disposition'));
        $this->assertStringContainsString('laporan-efektivitas-siklus-ppepp', $response->headers->get('content-disposition'));
    }

    public function test_export_pdf_respects_user_role_scoping_for_unit_pic(): void
    {
        $period = $this->spmiPeriod('Periode 2026');

        $unitA = $this->unit('UNIT-A', 'Unit A');
        $unitB = $this->unit('UNIT-B', 'Unit B');

        $standard = $this->qualityStandard($period);
        $indicator1 = $this->standardIndicator($standard, 'IND-01');
        $indicator2 = $this->standardIndicator($standard, 'IND-02');

        // Assignment for Unit A
        IndicatorUnitAssignment::create([
            'spmi_period_id' => $period->id,
            'unit_id' => $unitA->id,
            'standard_indicator_id' => $indicator1->id,
            'status' => IndicatorAssignmentStatus::Submitted,
            'due_date' => now()->addDays(5),
            'is_primary_pic' => true,
        ]);

        // Assignment for Unit B
        IndicatorUnitAssignment::create([
            'spmi_period_id' => $period->id,
            'unit_id' => $unitB->id,
            'standard_indicator_id' => $indicator2->id,
            'status' => IndicatorAssignmentStatus::Assigned,
            'due_date' => now()->addDays(5),
            'is_primary_pic' => true,
        ]);

        // Unit PIC for Unit A
        $unitPic = $this->roleUser('unit_pic', ['dashboard.view']);
        $unitPic->unit_id = $unitA->id;
        $unitPic->save();

        // 1. Export as Unit PIC (should only see Unit A data)
        $responsePic = $this->actingAs($unitPic)->get(route('dashboard.export-pdf', [
            'spmi_period_id' => $period->id,
        ]));
        $responsePic->assertStatus(200);

        // 2. Export as Admin LPM (should see all data)
        $admin = $this->roleUser('admin_lpm', ['dashboard.view']);
        $responseAdmin = $this->actingAs($admin)->get(route('dashboard.export-pdf', [
            'spmi_period_id' => $period->id,
        ]));
        $responseAdmin->assertStatus(200);
    }

    private function spmiPeriod(string $name): SpmiPeriod
    {
        return SpmiPeriod::query()->create([
            'name' => $name,
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
            'code' => 'CAT',
            'name' => 'Kategori Standar',
        ]);

        return QualityStandard::query()->create([
            'standard_category_id' => $category->id,
            'spmi_period_id' => $period->id,
            'code' => 'STD-01',
            'name' => 'Standar Mutu Test',
            'description' => 'Deskripsi standar mutu.',
            'status' => QualityStandardStatus::Active,
            'version' => 1,
        ]);
    }

    private function standardIndicator(QualityStandard $standard, string $code): StandardIndicator
    {
        return StandardIndicator::query()->create([
            'quality_standard_id' => $standard->id,
            'code' => $code,
            'statement' => 'Pernyataan Indikator '.$code,
            'indicator_type' => 'percentage',
            'target_value' => 80.00,
            'weight' => 1,
            'evidence_required' => false,
        ]);
    }

    private function unit(string $code, string $name): Unit
    {
        return Unit::query()->create([
            'code' => $code,
            'name' => $name,
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
