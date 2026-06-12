<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\InteractsWithRolePermissionForm;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use UnitEnum;

class RolePermissionManager extends Page implements HasTable
{
    use InteractsWithRolePermissionForm;
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Role & Permission';

    protected static ?string $title = 'Role & Permission';

    protected string $view = 'filament.pages.role-permission-manager';

    public static function canAccess(): bool
    {
        return (bool) (auth()->user()?->can('roles.view') || auth()->user()?->can('permissions.view'));
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Role::query()->with(['permissions'])->withCount(['permissions', 'users']))
            ->columns([
                TextColumn::make('name')
                    ->label('Role')
                    ->formatStateUsing(fn (string $state): string => str($state)->replace(['_', '-'], ' ')->headline()->toString())
                    ->searchable()
                    ->sortable(),
                TextColumn::make('permissions_count')
                    ->label('Jumlah Akses')
                    ->badge()
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('users_count')
                    ->label('Pengguna')
                    ->badge()
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('permissions.name')
                    ->label('Akses')
                    ->listWithLineBreaks()
                    ->formatStateUsing(fn (string $state): string => $this->permissionLabel($state))
                    ->limitList(4)
                    ->expandableLimitedList()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('guard_name')
                    ->label('Guard')
                    ->options(fn (): array => Role::query()
                        ->distinct()
                        ->orderBy('guard_name')
                        ->pluck('guard_name', 'guard_name')
                        ->all()),
            ])
            ->emptyStateHeading('Belum ada role')
            ->emptyStateDescription('Tambahkan role, lalu kelompokkan akses sesuai kebutuhan pengguna aplikasi.')
            ->emptyStateIcon(Heroicon::OutlinedKey)
            ->headerActions([
                Action::make('createRole')
                    ->label('Tambah Role')
                    ->icon(Heroicon::Plus)
                    ->color('primary')
                    ->url(fn (): string => CreateRolePermissionRole::getUrl())
                    ->visible(fn (): bool => (bool) auth()->user()?->can('roles.create')),
                Action::make('createPermission')
                    ->label('Tambah Permission')
                    ->icon(Heroicon::OutlinedPlus)
                    ->color('gray')
                    ->modalHeading('Tambah Permission')
                    ->schema([
                        Section::make('Permission')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Nama Permission')
                                            ->placeholder('contoh: reports.export')
                                            ->required()
                                            ->maxLength(255)
                                            ->unique(Permission::class, 'name'),
                                        Select::make('guard_name')
                                            ->label('Guard')
                                            ->options([
                                                'web' => 'web',
                                            ])
                                            ->default('web')
                                            ->required(),
                                    ]),
                            ]),
                    ])
                    ->visible(fn (): bool => (bool) auth()->user()?->can('permissions.create'))
                    ->action(function (array $data): void {
                        Permission::create($data);
                        $this->forgetPermissionCache();

                        Notification::make()
                            ->success()
                            ->title('Permission berhasil dibuat.')
                            ->send();
                    }),
            ])
            ->recordActions([
                Action::make('editRole')
                    ->label('Ubah')
                    ->icon(Heroicon::PencilSquare)
                    ->url(fn (Role $record): string => EditRolePermissionRole::getUrl(['role' => $record->getKey()]))
                    ->visible(fn (): bool => (bool) auth()->user()?->can('roles.update')),
                DeleteAction::make()
                    ->visible(fn (Role $record): bool => (bool) auth()->user()?->can('roles.delete')
                        && ! in_array($record->name, $this->systemRoles(), true))
                    ->after(function (): void {
                        $this->forgetPermissionCache();
                    }),
            ])
            ->defaultSort('name');
    }

    /**
     * @return array<int, string>
     */
    private function systemRoles(): array
    {
        return [
            'super_admin',
            'admin_lpm',
            'pimpinan',
            'unit_pic',
            'auditor',
            'viewer',
        ];
    }
}
