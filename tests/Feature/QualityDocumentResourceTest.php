<?php

namespace Tests\Feature;

use App\Enums\QualityDocumentStatus;
use App\Enums\QualityDocumentType;
use App\Enums\QualityStandardStatus;
use App\Enums\SpmiPeriodStatus;
use App\Filament\Resources\QualityDocuments\Pages\CreateQualityDocument;
use App\Filament\Resources\QualityDocuments\Pages\ListQualityDocuments;
use App\Models\QualityDocument;
use App\Models\QualityStandard;
use App\Models\SpmiPeriod;
use App\Models\StandardCategory;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class QualityDocumentResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_admin_lpm_can_create_quality_document_draft(): void
    {
        $admin = $this->createRoleUser('admin_lpm', [
            'quality-documents.view',
            'quality-documents.create',
        ]);
        $standard = $this->createQualityStandard();
        $period = $this->createSpmiPeriod();

        $this->actingAs($admin);

        Livewire::test(CreateQualityDocument::class)
            ->fillForm([
                'quality_standard_id' => $standard->id,
                'spmi_period_id' => $period->id,
                'title' => 'Manual Mutu Akademik',
                'document_type' => QualityDocumentType::Manual->value,
                'document_number' => 'MM-001',
                'version' => 1,
                'status' => QualityDocumentStatus::Draft->value,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(QualityDocument::class, [
            'title' => 'Manual Mutu Akademik',
            'document_type' => QualityDocumentType::Manual->value,
            'status' => QualityDocumentStatus::Draft->value,
            'uploaded_by' => $admin->id,
        ]);
    }

    public function test_active_document_requires_file_or_external_url(): void
    {
        $admin = $this->createRoleUser('admin_lpm', [
            'quality-documents.view',
            'quality-documents.create',
        ]);

        $this->actingAs($admin);

        Livewire::test(CreateQualityDocument::class)
            ->fillForm([
                'title' => 'SOP Audit Mutu Internal',
                'document_type' => QualityDocumentType::Sop->value,
                'version' => 1,
                'status' => QualityDocumentStatus::Active->value,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'file_path' => 'required',
                'external_url' => 'required',
            ]);
    }

    public function test_pimpinan_can_approve_document_and_admin_can_archive(): void
    {
        $pimpinan = $this->createRoleUser('pimpinan', [
            'quality-documents.view',
            'quality-documents.approve',
        ]);
        $admin = $this->createRoleUser('admin_lpm', [
            'quality-documents.view',
            'quality-documents.update',
        ]);
        $document = $this->createQualityDocument([
            'external_url' => 'https://example.com/manual.pdf',
            'status' => QualityDocumentStatus::Draft,
        ]);

        $this->actingAs($pimpinan);

        Livewire::test(ListQualityDocuments::class)
            ->callAction(TestAction::make('approve')->table($document))
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas(QualityDocument::class, [
            'id' => $document->id,
            'status' => QualityDocumentStatus::Active->value,
            'approved_by' => $pimpinan->id,
        ]);

        $this->actingAs($admin);

        Livewire::test(ListQualityDocuments::class)
            ->callAction(TestAction::make('archive')->table($document->refresh()))
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas(QualityDocument::class, [
            'id' => $document->id,
            'status' => QualityDocumentStatus::Archived->value,
        ]);
    }

    public function test_unit_and_viewer_only_see_active_documents(): void
    {
        $unitUser = $this->createRoleUser('unit_pic', [
            'quality-documents.view',
        ]);
        $activeDocument = $this->createQualityDocument([
            'title' => 'Pedoman Aktif',
            'status' => QualityDocumentStatus::Active,
        ]);
        $draftDocument = $this->createQualityDocument([
            'title' => 'Draf Internal',
            'status' => QualityDocumentStatus::Draft,
        ]);

        $this->actingAs($unitUser);

        Livewire::test(ListQualityDocuments::class)
            ->assertCanSeeTableRecords([$activeDocument])
            ->assertCanNotSeeTableRecords([$draftDocument]);
    }

    public function test_signed_file_route_streams_authorized_active_document(): void
    {
        $viewer = $this->createRoleUser('viewer', [
            'quality-documents.view',
        ]);
        $disk = config('filament.default_filesystem_disk');
        Storage::fake($disk);
        Storage::disk($disk)->put('quality-documents/manual.pdf', 'PDF');
        $document = $this->createQualityDocument([
            'file_path' => 'quality-documents/manual.pdf',
            'status' => QualityDocumentStatus::Active,
        ]);

        $this->actingAs($viewer);

        $url = URL::signedRoute('quality-documents.file', ['document' => $document], absolute: false);

        $this->get($url)
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createQualityDocument(array $overrides = []): QualityDocument
    {
        return QualityDocument::query()->create([
            'quality_standard_id' => $overrides['quality_standard_id'] ?? $this->createQualityStandard()->id,
            'spmi_period_id' => $overrides['spmi_period_id'] ?? $this->createSpmiPeriod()->id,
            'title' => $overrides['title'] ?? 'Dokumen Mutu',
            'document_type' => $overrides['document_type'] ?? QualityDocumentType::Manual,
            'document_number' => $overrides['document_number'] ?? 'DOC-001',
            'version' => $overrides['version'] ?? 1,
            'file_path' => $overrides['file_path'] ?? null,
            'external_url' => $overrides['external_url'] ?? 'https://example.com/dokumen.pdf',
            'status' => $overrides['status'] ?? QualityDocumentStatus::Draft,
        ]);
    }

    private function createQualityStandard(): QualityStandard
    {
        $category = StandardCategory::query()->firstOrCreate(
            ['code' => 'STD'],
            ['name' => 'Standar', 'description' => null],
        );

        return QualityStandard::query()->create([
            'standard_category_id' => $category->id,
            'code' => 'STD-'.fake()->unique()->numerify('###'),
            'name' => 'Standar Mutu '.fake()->unique()->word(),
            'status' => QualityStandardStatus::Draft,
            'version' => 1,
        ]);
    }

    private function createSpmiPeriod(): SpmiPeriod
    {
        return SpmiPeriod::query()->create([
            'name' => 'SPMI '.fake()->unique()->year(),
            'academic_year' => '2025/2026',
            'semester' => null,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => SpmiPeriodStatus::Active,
        ]);
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
        $role->givePermissionTo($permissions);

        return User::factory()->create()->assignRole($role);
    }
}
