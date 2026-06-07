<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TopbarRoleTextTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_displays_the_authenticated_users_role_text(): void
    {
        $role = Role::findOrCreate('super_admin', 'web');
        $user = User::factory()->create()->assignRole($role);

        $this->actingAs($user);

        $this->view('filament.topbar.user-role')
            ->assertSeeText('Role: Super Admin');
    }

    public function test_it_displays_fallback_text_when_user_has_no_role(): void
    {
        $this->actingAs(User::factory()->create());

        $this->view('filament.topbar.user-role')
            ->assertSeeText('Role: Tanpa Role');
    }
}
