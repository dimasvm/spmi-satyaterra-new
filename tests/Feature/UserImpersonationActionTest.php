<?php

namespace Tests\Feature;

use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserImpersonationActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_authorized_user_can_impersonate_active_user_from_users_table(): void
    {
        $admin = $this->createAdminWithImpersonationPermission();
        $target = User::factory()->create([
            'is_active' => true,
        ]);

        $this->actingAs($admin);

        Livewire::test(ListUsers::class)
            ->callAction(TestAction::make('impersonate')->table($target));

        $this->assertAuthenticatedAs($target);
        $this->assertSame($admin->id, session('impersonator_id'));
    }

    public function test_impersonate_action_is_hidden_for_inactive_users(): void
    {
        $admin = $this->createAdminWithImpersonationPermission();
        $target = User::factory()->create([
            'is_active' => false,
        ]);

        $this->actingAs($admin);

        Livewire::test(ListUsers::class)
            ->assertTableActionHidden('impersonate', $target);
    }

    private function createAdminWithImpersonationPermission(): User
    {
        Permission::findOrCreate('users.impersonate', 'web');

        $role = Role::findOrCreate('super_admin', 'web');
        $role->givePermissionTo('users.impersonate');

        return User::factory()
            ->create([
                'is_active' => true,
            ])
            ->assignRole($role);
    }
}
