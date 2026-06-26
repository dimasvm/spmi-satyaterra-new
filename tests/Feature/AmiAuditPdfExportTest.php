<?php

namespace Tests\Feature;

use App\Enums\AmiAuditStatus;
use App\Enums\AmiPeriodStatus;
use App\Enums\SpmiPeriodStatus;
use App\Models\AmiAudit;
use App\Models\AmiPeriod;
use App\Models\SpmiPeriod;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AmiAuditPdfExportTest extends TestCase
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

        // Pre-create roles to avoid Spatie exceptions during role query scoping
        Role::findOrCreate('unit_pic', 'web');
        Role::findOrCreate('admin_lpm', 'web');
        Role::findOrCreate('auditor', 'web');
    }

    public function test_guest_cannot_export_ami_audit_pdf(): void
    {
        $unit = $this->unit('UNIT-A', 'Unit A');
        $audit = $this->createAmiAudit($unit);

        $response = $this->get(route('ami-audits.export-pdf', $audit));

        $response->assertRedirect(route('login'));
    }

    public function test_user_without_export_permission_cannot_export(): void
    {
        $user = $this->roleUser('viewer', []);
        $unit = $this->unit('UNIT-A', 'Unit A');
        $audit = $this->createAmiAudit($unit);

        $response = $this->actingAs($user)->get(route('ami-audits.export-pdf', $audit));

        $response->assertStatus(403);
    }

    public function test_authorized_user_can_export_ami_audit_pdf(): void
    {
        $user = $this->roleUser('admin_lpm', ['ami-audits.export']);
        $unit = $this->unit('UNIT-A', 'Unit A');
        $audit = $this->createAmiAudit($unit);

        $response = $this->actingAs($user)->get(route('ami-audits.export-pdf', $audit));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
        $this->assertStringContainsString('attachment;', $response->headers->get('content-disposition'));
        $this->assertStringContainsString('laporan-ami-unit-a', $response->headers->get('content-disposition'));
    }

    public function test_unit_pic_can_only_export_their_own_unit_ami_audit(): void
    {
        $unitA = $this->unit('UNIT-A', 'Unit A');
        $unitB = $this->unit('UNIT-B', 'Unit B');

        $auditA = $this->createAmiAudit($unitA);
        $auditB = $this->createAmiAudit($unitB);

        // PIC for Unit A
        $unitPicA = $this->roleUser('unit_pic', ['ami-audits.export']);
        $unitPicA->unit_id = $unitA->id;
        $unitPicA->save();

        // 1. Export own unit (should succeed)
        $responseOwn = $this->actingAs($unitPicA)->get(route('ami-audits.export-pdf', $auditA));
        $responseOwn->assertStatus(200);

        // 2. Export other unit (should be 404 due to forUser scoping in controller query)
        $responseOther = $this->actingAs($unitPicA)->get(route('ami-audits.export-pdf', $auditB));
        $responseOther->assertStatus(404);
    }

    private function createAmiAudit(Unit $unit): AmiAudit
    {
        $spmiPeriod = SpmiPeriod::create([
            'name' => 'Periode SPMI 2026',
            'academic_year' => '2026/2027',
            'semester' => 'ganjil',
            'start_date' => '2026-07-01',
            'end_date' => '2026-12-31',
            'status' => SpmiPeriodStatus::Active,
        ]);

        $amiPeriod = AmiPeriod::create([
            'spmi_period_id' => $spmiPeriod->id,
            'name' => 'Periode AMI 2026',
            'start_date' => '2026-08-01',
            'end_date' => '2026-09-30',
            'status' => AmiPeriodStatus::Ongoing,
        ]);

        return AmiAudit::create([
            'ami_period_id' => $amiPeriod->id,
            'auditee_unit_id' => $unit->id,
            'scheduled_date' => '2026-08-15',
            'status' => AmiAuditStatus::Planned,
        ]);
    }

    private function unit(string $code, string $name): Unit
    {
        return Unit::create([
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
