<?php

namespace Tests\Feature;

use App\Enums\QualityStandardStatus;
use App\Filament\Resources\QualityStandards\Pages\ListQualityStandards;
use App\Models\QualityStandard;
use App\Models\StandardCategory;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ManageStandardCategoriesTableTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $role = Role::findOrCreate('admin_lpm', 'web');
        $role->givePermissionTo(Permission::findOrCreate('quality-standards.view', 'web'));

        $this->actingAs(User::factory()->create()->assignRole($role));
    }

    public function test_quality_standards_list_has_manage_standard_categories_header_action(): void
    {
        Livewire::test(ListQualityStandards::class)
            ->assertOk()
            ->assertSee('Kategori Standar');
    }

    public function test_it_can_list_standard_categories(): void
    {
        $category = StandardCategory::query()->create([
            'code' => 'STD',
            'name' => 'Standar',
            'description' => 'Kategori standar mutu.',
        ]);

        Livewire::test('manage-standard-categories-table')
            ->assertCanSeeTableRecords([$category]);
    }

    public function test_it_can_create_standard_category(): void
    {
        Livewire::test('manage-standard-categories-table')
            ->callAction(TestAction::make(CreateAction::class)->table(), [
                'code' => 'EDU',
                'name' => 'Pendidikan',
                'description' => 'Kategori pendidikan.',
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas(StandardCategory::class, [
            'code' => 'EDU',
            'name' => 'Pendidikan',
        ]);
    }

    public function test_it_can_create_standard_subcategory_under_parent_category(): void
    {
        $parent = StandardCategory::query()->create([
            'code' => 'PDD',
            'name' => 'Pendidikan',
            'description' => null,
        ]);

        Livewire::test('manage-standard-categories-table')
            ->callAction(TestAction::make(CreateAction::class)->table(), [
                'parent_id' => $parent->id,
                'code' => 'PDD-MSK',
                'name' => 'Masukan',
                'description' => 'Subkategori masukan pendidikan.',
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas(StandardCategory::class, [
            'parent_id' => $parent->id,
            'code' => 'PDD-MSK',
            'name' => 'Masukan',
        ]);
    }

    public function test_it_can_update_standard_category(): void
    {
        $category = StandardCategory::query()->create([
            'code' => 'OLD',
            'name' => 'Nama Lama',
            'description' => null,
        ]);

        Livewire::test('manage-standard-categories-table')
            ->callAction(TestAction::make(EditAction::class)->table($category), [
                'code' => 'NEW',
                'name' => 'Nama Baru',
                'description' => 'Deskripsi baru.',
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas(StandardCategory::class, [
            'id' => $category->id,
            'code' => 'NEW',
            'name' => 'Nama Baru',
        ]);
    }

    public function test_delete_action_is_visible_for_unused_standard_category(): void
    {
        $category = StandardCategory::query()->create([
            'code' => 'UNUSED',
            'name' => 'Belum Dipakai',
            'description' => null,
        ]);

        Livewire::test('manage-standard-categories-table')
            ->assertActionVisible(TestAction::make(DeleteAction::class)->table($category));
    }

    public function test_delete_action_is_hidden_for_used_standard_category(): void
    {
        $category = StandardCategory::query()->create([
            'code' => 'USED',
            'name' => 'Sudah Dipakai',
            'description' => null,
        ]);

        QualityStandard::query()->create([
            'standard_category_id' => $category->id,
            'code' => 'QS-001',
            'name' => 'Standar Mutu 001',
            'status' => QualityStandardStatus::Draft,
            'version' => 1,
        ]);

        Livewire::test('manage-standard-categories-table')
            ->assertActionHidden(TestAction::make(DeleteAction::class)->table($category));
    }

    public function test_delete_action_is_hidden_for_parent_category_with_children(): void
    {
        $parent = StandardCategory::query()->create([
            'code' => 'PDD',
            'name' => 'Pendidikan',
            'description' => null,
        ]);

        StandardCategory::query()->create([
            'parent_id' => $parent->id,
            'code' => 'PDD-PRS',
            'name' => 'Proses',
            'description' => null,
        ]);

        Livewire::test('manage-standard-categories-table')
            ->assertActionHidden(TestAction::make(DeleteAction::class)->table($parent));
    }
}
