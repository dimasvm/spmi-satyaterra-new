<?php

namespace Tests\Feature;

use App\Enums\IndicatorAssignmentStatus;
use App\Enums\QualityStandardStatus;
use App\Enums\SpmiPeriodStatus;
use App\Enums\StandardIndicatorType;
use App\Enums\TargetOperator;
use App\Filament\Pages\AuditSaya;
use App\Filament\Pages\CapaianIndikatorSaya;
use App\Filament\Pages\InboxValidasiCapaian;
use App\Filament\Pages\MonitoringTemuan;
use App\Filament\Pages\TemuanSaya;
use App\Filament\Pages\VerifikasiTindakLanjut;
use App\Models\IndicatorUnitAssignment;
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

class UiPolishConsistencyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_auditor_empty_audit_page_has_search_and_specific_empty_state(): void
    {
        $this->actingAs($this->roleUser('auditor'));

        Livewire::test(AuditSaya::class)
            ->set('search', 'Audit')
            ->assertSee('Cari audit')
            ->assertSee('Belum ada audit yang ditugaskan kepada Anda.');
    }

    public function test_unit_work_pages_have_search_and_clear_empty_states(): void
    {
        $unitUser = $this->roleUser('unit_pic', [
            'corrective-actions.view',
        ], withUnit: true);

        $this->actingAs($unitUser);

        Livewire::test(CapaianIndikatorSaya::class)
            ->set('search', 'IKU')
            ->assertSee('Cari capaian')
            ->assertSee('Belum ada capaian yang sesuai.');

        Livewire::test(TemuanSaya::class)
            ->set('search', 'Temuan')
            ->assertSee('Cari temuan')
            ->assertSee('Semua temuan sudah ditindaklanjuti.');

        $this->assertStringContainsString(
            'Submit Tindak Lanjut',
            file_get_contents(resource_path('views/filament/pages/temuan-saya.blade.php')),
        );
    }

    public function test_unit_indicator_search_can_find_assignment_by_indicator_code(): void
    {
        $unitUser = $this->roleUser('unit_pic', withUnit: true);

        $this->actingAs($unitUser);

        $period = SpmiPeriod::query()->create([
            'name' => 'SPMI 2026',
            'academic_year' => '2026/2027',
            'semester' => 'ganjil',
            'start_date' => '2026-07-01',
            'end_date' => '2026-12-31',
            'status' => SpmiPeriodStatus::Active,
        ]);

        $category = StandardCategory::query()->create([
            'code' => 'STD',
            'name' => 'Standar Pendidikan',
        ]);

        $standard = QualityStandard::query()->create([
            'standard_category_id' => $category->id,
            'spmi_period_id' => $period->id,
            'code' => 'STD-01',
            'name' => 'Standar Pembelajaran',
            'status' => QualityStandardStatus::Active,
            'version' => 1,
        ]);

        $indicator = StandardIndicator::query()->create([
            'quality_standard_id' => $standard->id,
            'code' => 'IKU-SEARCH',
            'statement' => 'Indikator pencarian',
            'indicator_type' => StandardIndicatorType::Percentage,
            'target_operator' => TargetOperator::GreaterThanOrEqual,
            'target_value' => 80,
            'target_unit' => '%',
        ]);

        IndicatorUnitAssignment::query()->create([
            'standard_indicator_id' => $indicator->id,
            'unit_id' => $unitUser->unit_id,
            'spmi_period_id' => $period->id,
            'due_date' => '2026-12-31',
            'status' => IndicatorAssignmentStatus::Assigned,
        ]);

        Livewire::test(CapaianIndikatorSaya::class)
            ->set('search', 'IKU-SEARCH')
            ->assertSee('IKU-SEARCH')
            ->assertSee('Indikator pencarian');
    }

    public function test_validation_pages_use_consistent_action_labels(): void
    {
        $admin = $this->roleUser('admin_lpm', [
            'indicator-achievements.review',
        ]);

        $this->actingAs($admin);

        Livewire::test(InboxValidasiCapaian::class)
            ->set('search', 'Capaian')
            ->assertSee('Cari capaian')
            ->assertSee('Belum ada capaian yang perlu divalidasi.');

        Livewire::test(MonitoringTemuan::class)
            ->set('search', 'Temuan')
            ->assertSee('Pencarian')
            ->assertSee('Tidak ada temuan sesuai filter.');

        $auditor = $this->roleUser('auditor', [
            'corrective-actions.review',
        ]);

        $this->actingAs($auditor);

        Livewire::test(VerifikasiTindakLanjut::class)
            ->set('search', 'Tindak lanjut')
            ->assertSee('Cari tindak lanjut')
            ->assertSee('Semua temuan sudah ditindaklanjuti.');

        $this->assertStringContainsString(
            'Terima Perbaikan',
            file_get_contents(resource_path('views/filament/pages/verifikasi-tindak-lanjut.blade.php')),
        );
    }

    /**
     * @param  array<int, string>  $permissions
     */
    private function roleUser(string $roleName, array $permissions = [], bool $withUnit = false): User
    {
        $role = Role::findOrCreate($roleName, 'web');

        foreach ($permissions as $permission) {
            $role->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }

        $unitId = null;

        if ($withUnit) {
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
