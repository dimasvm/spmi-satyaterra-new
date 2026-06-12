<?php

namespace Tests\Feature;

use App\Filament\Pages\CreateRolePermissionRole;
use App\Filament\Pages\EditRolePermissionRole;
use App\Filament\Pages\RolePermissionManager;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolePermissionManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_admin_can_create_permission_and_role_from_custom_page(): void
    {
        $admin = $this->createAccessAdmin();

        $this->actingAs($admin);

        Livewire::test(RolePermissionManager::class)
            ->callAction(TestAction::make('createPermission')->table(), [
                'name' => 'reports.download',
                'guard_name' => 'web',
            ])
            ->assertHasNoActionErrors();

        Livewire::test(CreateRolePermissionRole::class)
            ->fillForm([
                'name' => 'report_viewer',
                'guard_name' => 'web',
                'permissions' => ['reports.download'],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Permission::class, [
            'name' => 'reports.download',
            'guard_name' => 'web',
        ]);

        $role = Role::query()->where('name', 'report_viewer')->firstOrFail();

        $this->assertTrue($role->hasPermissionTo('reports.download'));
    }

    public function test_admin_can_update_role_permissions_from_custom_page(): void
    {
        $admin = $this->createAccessAdmin();
        $role = Role::findOrCreate('operator', 'web');
        Permission::findOrCreate('reports.view', 'web');
        Permission::findOrCreate('reports.export', 'web');

        $role->givePermissionTo('reports.view');

        $this->actingAs($admin);

        Livewire::test(EditRolePermissionRole::class, ['role' => $role->id])
            ->fillForm([
                'name' => 'operator',
                'guard_name' => 'web',
                'permissions' => ['reports.export'],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $role->refresh();

        $this->assertFalse($role->hasPermissionTo('reports.view'));
        $this->assertTrue($role->hasPermissionTo('reports.export'));
    }

    public function test_permission_column_uses_indonesian_labels(): void
    {
        $admin = $this->createAccessAdmin();
        $role = Role::findOrCreate('report_operator', 'web');
        Permission::findOrCreate('reports.view', 'web');
        $role->givePermissionTo('reports.view');

        $this->actingAs($admin);

        Livewire::test(RolePermissionManager::class)
            ->assertSee('Melihat Laporan');
    }

    public function test_role_form_does_not_query_permissions_per_module(): void
    {
        $admin = $this->createAccessAdmin();

        foreach (range(1, 30) as $number) {
            Permission::findOrCreate("module-{$number}.view", 'web');
            Permission::findOrCreate("module-{$number}.create", 'web');
        }

        $this->actingAs($admin);

        $permissionQueries = 0;
        DB::listen(function ($query) use (&$permissionQueries): void {
            if (str_contains($query->sql, 'permissions')) {
                $permissionQueries++;
            }
        });

        Livewire::test(CreateRolePermissionRole::class)
            ->assertSee('Cari modul atau akses');

        $this->assertLessThan(10, $permissionQueries);
    }

    private function createAccessAdmin(): User
    {
        foreach ([
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',
            'permissions.view',
            'permissions.create',
        ] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $role = Role::findOrCreate('admin_lpm', 'web');
        $role->syncPermissions([
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',
            'permissions.view',
            'permissions.create',
        ]);

        return User::factory()->create()->assignRole($role);
    }
}
