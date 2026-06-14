<?php

namespace Tests\Feature;

use App\Filament\Pages\PanduanPenggunaan;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TopbarRoleTextTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_it_displays_the_authenticated_users_role_text(): void
    {
        $role = Role::findOrCreate('super_admin', 'web');
        $user = User::factory()->create()->assignRole($role);

        $this->actingAs($user);

        $this->view('filament.topbar.user-role')
            ->assertSeeText('Super Admin');
    }

    public function test_it_displays_fallback_text_when_user_has_no_role(): void
    {
        $this->actingAs(User::factory()->create());

        $this->view('filament.topbar.user-role')
            ->assertSeeText('Tanpa Role');
    }

    public function test_it_displays_guide_icon_link_in_the_topbar(): void
    {
        $this->view('filament.topbar.guide-link', [
            'url' => PanduanPenggunaan::getUrl(),
        ])
            ->assertSee('Panduan Penggunaan')
            ->assertSee('target="_blank"', false)
            ->assertSee(PanduanPenggunaan::getUrl(), false);
    }

    public function test_authenticated_user_can_open_the_usage_guide_page(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(PanduanPenggunaan::getUrl())
            ->assertOk()
            ->assertSee('Panduan Penggunaan Aplikasi SPMI')
            ->assertSee('Admin LPM');
    }
}
