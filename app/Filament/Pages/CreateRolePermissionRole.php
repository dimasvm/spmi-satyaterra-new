<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\InteractsWithRolePermissionForm;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Spatie\Permission\Models\Role;

class CreateRolePermissionRole extends Page
{
    use InteractsWithRolePermissionForm;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'role-permission-manager/create-role';

    protected static ?string $title = 'Tambah Role';

    protected string $view = 'filament.pages.role-permission-role-form';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->can('roles.create');
    }

    public function mount(): void
    {
        $this->form->fill([
            'guard_name' => 'web',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components($this->roleFormSchema())
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $role = Role::create([
            'name' => $data['name'],
            'guard_name' => $data['guard_name'],
        ]);

        $role->syncPermissions($this->selectedPermissions($data));
        $this->forgetPermissionCache();

        Notification::make()
            ->success()
            ->title('Role berhasil dibuat.')
            ->send();

        $this->redirect(RolePermissionManager::getUrl(), navigate: true);
    }

    /**
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Kembali')
                ->icon(Heroicon::ArrowLeft)
                ->color('gray')
                ->url(RolePermissionManager::getUrl()),
        ];
    }
}
