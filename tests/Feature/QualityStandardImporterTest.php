<?php

namespace Tests\Feature;

use App\Enums\QualityStandardStatus;
use App\Enums\SpmiPeriodStatus;
use App\Enums\StandardIndicatorType;
use App\Enums\TargetOperator;
use App\Enums\UnitType;
use App\Filament\Imports\QualityStandardImporter;
use App\Filament\Resources\QualityStandards\Pages\ListQualityStandards;
use App\Models\QualityStandard;
use App\Models\SpmiPeriod;
use App\Models\StandardCategory;
use App\Models\StandardIndicator;
use App\Models\StandardStatement;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class QualityStandardImporterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_admin_can_import_quality_standard_with_indicator(): void
    {
        $user = $this->createRoleUser('admin_lpm', $this->importPermissions());
        $period = $this->createSpmiPeriod();

        $this->actingAs($user);

        $this->runImporter($user, [
            'standard_code' => 'STD-001',
            'standard_name' => 'Standar Kompetensi Lulusan',
            'standard_category_code' => 'SKL',
            'standard_category_name' => 'Standar Kompetensi',
            'standard_subcategory_code' => 'SKL-LRN',
            'standard_subcategory_name' => 'Luaran',
            'scope_type' => 'Program Studi',
            'spmi_period_name' => $period->name,
            'standard_statement' => 'Program studi menetapkan capaian pembelajaran lulusan.',
            'standard_description' => 'Standar lulusan.',
            'standard_status' => 'Aktif',
            'standard_version' => '2',
            'indicator_code' => 'IKU-001',
            'indicator_statement' => 'Persentase lulusan tepat waktu.',
            'indicator_type' => 'Persentase',
            'target_operator' => '>=',
            'target_value' => '80',
            'target_unit' => '%',
            'weight' => '3',
            'evidence_required' => 'yes',
            'evidence_description' => 'Dokumen tracer study.',
        ]);

        $this->assertDatabaseHas(StandardCategory::class, [
            'code' => 'SKL',
            'name' => 'Standar Kompetensi',
        ]);

        $category = StandardCategory::query()->where('code', 'SKL')->firstOrFail();

        $this->assertDatabaseHas(StandardCategory::class, [
            'parent_id' => $category->id,
            'code' => 'SKL-LRN',
            'name' => 'Luaran',
        ]);

        $this->assertDatabaseHas(QualityStandard::class, [
            'code' => 'STD-001',
            'name' => 'Standar Kompetensi Lulusan',
            'spmi_period_id' => $period->id,
            'scope_type' => UnitType::StudyProgram->value,
            'status' => QualityStandardStatus::Active->value,
            'version' => 2,
        ]);

        $standard = QualityStandard::query()->where('code', 'STD-001')->firstOrFail();
        $statement = StandardStatement::query()
            ->where('quality_standard_id', $standard->id)
            ->where('code', 'PS-001')
            ->firstOrFail();

        $this->assertDatabaseHas(StandardStatement::class, [
            'id' => $statement->id,
            'quality_standard_id' => $standard->id,
            'statement' => 'Program studi menetapkan capaian pembelajaran lulusan.',
        ]);

        $this->assertDatabaseHas(StandardIndicator::class, [
            'quality_standard_id' => $standard->id,
            'standard_statement_id' => $statement->id,
            'code' => 'IKU-001',
            'statement' => 'Persentase lulusan tepat waktu.',
            'indicator_type' => StandardIndicatorType::Percentage->value,
            'target_operator' => TargetOperator::GreaterThanOrEqual->value,
            'target_value' => 80,
            'target_unit' => '%',
            'weight' => 3,
            'evidence_required' => true,
        ]);
    }

    public function test_import_adds_multiple_indicators_to_existing_standard_without_duplicate_standard(): void
    {
        $user = $this->createRoleUser('admin_lpm', $this->importPermissions());
        $period = $this->createSpmiPeriod();

        $this->runImporter($user, [
            'standard_code' => 'STD-001',
            'standard_name' => 'Standar Mutu',
            'standard_category_code' => 'STD',
            'spmi_period_name' => $period->name,
            'indicator_code' => 'IKU-001',
            'indicator_statement' => 'Indikator pertama.',
        ]);

        $this->runImporter($user, [
            'standard_code' => 'STD-001',
            'standard_name' => 'Standar Mutu Revisi',
            'standard_category_code' => 'STD',
            'spmi_period_name' => $period->name,
            'indicator_code' => 'IKU-002',
            'indicator_statement' => 'Indikator kedua.',
            'target_value' => '5',
            'target_unit' => 'dokumen',
        ]);

        $this->assertSame(1, QualityStandard::query()->where('code', 'STD-001')->count());

        $standard = QualityStandard::query()->where('code', 'STD-001')->firstOrFail();

        $this->assertSame('Standar Mutu Revisi', $standard->name);
        $this->assertSame(2, $standard->indicators()->count());
    }

    public function test_import_fails_when_spmi_period_name_does_not_exist(): void
    {
        $user = $this->createRoleUser('admin_lpm', $this->importPermissions());

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Periode SPMI [SPMI Tidak Ada] tidak ditemukan.');

        $this->runImporter($user, [
            'standard_code' => 'STD-404',
            'standard_name' => 'Standar Tanpa Periode',
            'standard_category_code' => 'STD',
            'spmi_period_name' => 'SPMI Tidak Ada',
            'indicator_code' => 'IKU-404',
            'indicator_statement' => 'Indikator tidak valid.',
        ]);
    }

    public function test_importer_reads_xlsx_file_with_heading_row(): void
    {
        $user = $this->createRoleUser('admin_lpm', $this->importPermissions());
        $period = $this->createSpmiPeriod();
        $path = tempnam(sys_get_temp_dir(), 'standar-mutu-').'.xlsx';
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->fromArray([
            [
                'standard_code',
                'standard_name',
                'standard_category_code',
                'spmi_period_name',
                'indicator_code',
                'indicator_statement',
                'target_value',
            ],
            [
                'STD-XLSX',
                'Standar dari XLSX',
                'STD',
                $period->name,
                'IKU-XLSX',
                'Indikator dari file XLSX.',
                95,
            ],
        ]);

        (new Xlsx($spreadsheet))->save($path);

        try {
            Excel::import(new QualityStandardImporter($user), $path, null, ExcelFormat::XLSX);
        } finally {
            @unlink($path);
        }

        $standard = QualityStandard::query()->where('code', 'STD-XLSX')->firstOrFail();

        $this->assertDatabaseHas(StandardIndicator::class, [
            'quality_standard_id' => $standard->id,
            'code' => 'IKU-XLSX',
            'statement' => 'Indikator dari file XLSX.',
            'target_value' => 95,
        ]);
    }

    public function test_import_action_is_available_for_authorized_admin(): void
    {
        $user = $this->createRoleUser('admin_lpm', [
            ...$this->importPermissions(),
            'quality-standards.view',
        ]);

        $this->actingAs($user);

        Livewire::test(ListQualityStandards::class)
            ->assertActionVisible('downloadImportTemplate')
            ->assertActionVisible('importQualityStandards');
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function runImporter(User $user, array $row): void
    {
        (new QualityStandardImporter($user))->processRow($row);
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

    /**
     * @return array<int, string>
     */
    private function importPermissions(): array
    {
        return [
            'quality-standards.create',
            'quality-standards.update',
            'standard-indicators.create',
            'standard-indicators.update',
        ];
    }

    /**
     * @param  array<int, string>  $permissions
     */
    private function createRoleUser(string $roleName, array $permissions): User
    {
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $role = Role::findOrCreate($roleName, 'web');
        $role->syncPermissions($permissions);

        return User::factory()->create()->assignRole($role);
    }
}
