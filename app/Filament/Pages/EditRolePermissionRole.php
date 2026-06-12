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

class EditRolePermissionRole extends Page
{
    use InteractsWithRolePermissionForm;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'role-permission-manager/{role}/edit';

    protected static ?string $title = 'Ubah Role';

    protected string $view = 'filament.pages.role-permission-role-form';

    public Role $roleRecord;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->can('roles.update');
    }

    public function mount(int|string $role): void
    {
        $this->roleRecord = Role::query()->findOrFail($role);

        $this->form->fill([
            'name' => $this->roleRecord->name,
            'guard_name' => $this->roleRecord->guard_name,
            'permissions' => $this->roleRecord->permissions()->pluck('name')->all(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components($this->roleFormSchema())
            ->statePath('data')
            ->model($this->roleRecord);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $this->roleRecord->update([
            'name' => $data['name'],
            'guard_name' => $data['guard_name'],
        ]);

        $this->roleRecord->syncPermissions($this->selectedPermissions($data));
        $this->forgetPermissionCache();

        Notification::make()
            ->success()
            ->title('Role berhasil diperbarui.')
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
