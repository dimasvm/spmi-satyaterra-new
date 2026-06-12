<?php

namespace App\Filament\Pages\Concerns;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

trait InteractsWithRolePermissionForm
{
    protected ?Collection $cachedPermissionNames = null;

    /**
     * @return array<int, mixed>
     */
    protected function roleFormSchema(): array
    {
        return [
            Section::make('Informasi Role')
                ->description('Atur identitas role dan daftar akses yang melekat pada role ini.')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label('Nama Role')
                                ->placeholder('contoh: auditor_internal')
                                ->required()
                                ->maxLength(255)
                                ->unique(Role::class, 'name', ignoreRecord: true),
                            Select::make('guard_name')
                                ->label('Guard')
                                ->options([
                                    'web' => 'web',
                                ])
                                ->default('web')
                                ->required(),
                        ]),
                ])
                ->columnSpanFull(),
            Section::make('Akses Modul')
                ->description('Gunakan pencarian untuk menemukan modul atau jenis akses. Contoh: temuan, laporan, review, hapus.')
                ->schema([
                    CheckboxList::make('permissions')
                        ->hiddenLabel()
                        ->options(fn (): array => $this->permissionOptions())
                        ->descriptions(fn (): array => $this->permissionDescriptions())
                        ->bulkToggleable()
                        ->columns([
                            'default' => 1,
                            'md' => 2,
                            'xl' => 3,
                        ])
                        ->searchable()
                        ->searchDebounce(300)
                        ->searchPrompt('Cari modul atau akses')
                        ->noSearchResultsMessage('Tidak ada akses yang cocok.')
                        ->columnSpanFull(),
                ])
                ->columnSpanFull(),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function permissionOptions(): array
    {
        return $this->permissionNames()
            ->mapWithKeys(fn (string $permission): array => [
                $permission => $this->permissionLabel($permission),
            ])
            ->all();
    }

    /**
     * @return Collection<int, string>
     */
    protected function permissionNames(): Collection
    {
        if ($this->cachedPermissionNames instanceof Collection) {
            return $this->cachedPermissionNames;
        }

        return $this->cachedPermissionNames = Permission::query()
            ->orderBy('name')
            ->pluck('name');
    }

    /**
     * @return array<string, string>
     */
    protected function permissionDescriptions(): array
    {
        return $this->permissionNames()
            ->mapWithKeys(fn (string $permission): array => [
                $permission => $this->permissionDescription($permission),
            ])
            ->all();
    }

    protected function permissionGroupLabel(string $permission): string
    {
        $module = str($permission)->beforeLast('.')->toString();

        return $this->permissionModules()[$module]
            ?? str($module)->replace(['-', '_'], ' ')->headline()->toString();
    }

    protected function permissionLabel(string $permission): string
    {
        [$module, $action] = $this->splitPermission($permission);
        $moduleLabel = $this->permissionModules()[$module]
            ?? str($module)->replace(['-', '_'], ' ')->headline()->toString();

        return trim(($this->permissionActions()[$action] ?? str($action)->replace(['-', '_'], ' ')->headline()->toString()).' '.$moduleLabel);
    }

    protected function permissionSearchLabel(string $permission): string
    {
        [$module, $action] = $this->splitPermission($permission);
        $moduleLabel = $this->permissionModules()[$module]
            ?? str($module)->replace(['-', '_'], ' ')->headline()->toString();
        $actionLabel = $this->permissionActions()[$action]
            ?? str($action)->replace(['-', '_'], ' ')->headline()->toString();

        return $moduleLabel.' - '.$actionLabel;
    }

    protected function permissionDescription(string $permission): string
    {
        [$module, $action] = $this->splitPermission($permission);
        $moduleLabel = $this->permissionModules()[$module]
            ?? str($module)->replace(['-', '_'], ' ')->headline()->lower()->toString();

        return match ($action) {
            'view' => 'Mengizinkan pengguna membuka dan membaca data '.$moduleLabel.'.',
            'create' => 'Mengizinkan pengguna menambahkan data baru pada modul '.$moduleLabel.'.',
            'update' => 'Mengizinkan pengguna mengubah data pada modul '.$moduleLabel.'.',
            'delete' => 'Mengizinkan pengguna menghapus data pada modul '.$moduleLabel.'.',
            'submit' => 'Mengizinkan pengguna mengirim data untuk proses validasi atau verifikasi.',
            'review' => 'Mengizinkan pengguna memeriksa, menerima, atau meminta revisi data.',
            'approve' => 'Mengizinkan pengguna menyetujui data atau dokumen.',
            'finalize' => 'Mengizinkan pengguna memfinalisasi proses agar hasilnya terkunci.',
            'export' => 'Mengizinkan pengguna mengekspor data atau laporan.',
            'impersonate' => 'Mengizinkan pengguna masuk sebagai pengguna lain untuk kebutuhan bantuan teknis.',
            default => 'Permission teknis: '.$permission,
        };
    }

    /**
     * @return array{0: string, 1: string}
     */
    protected function splitPermission(string $permission): array
    {
        if (! str_contains($permission, '.')) {
            return [$permission, 'access'];
        }

        return [
            str($permission)->beforeLast('.')->toString(),
            str($permission)->afterLast('.')->toString(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<int, string>
     */
    protected function selectedPermissions(array $data): array
    {
        return collect($data['permissions'] ?? [])
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<string, string>
     */
    protected function permissionModules(): array
    {
        return [
            'activity-logs' => 'Log Aktivitas',
            'achievement-evidences' => 'Bukti Capaian',
            'achievement-reviews' => 'Review Capaian',
            'ami-audits' => 'Audit AMI',
            'ami-checklists' => 'Checklist AMI',
            'ami-findings' => 'Temuan AMI',
            'ami-periods' => 'Periode AMI',
            'corrective-action-evidences' => 'Bukti Tindak Lanjut',
            'corrective-actions' => 'Tindak Lanjut Temuan',
            'dashboard' => 'Dashboard',
            'indicator-achievements' => 'Capaian Indikator',
            'indicator-assignments' => 'Penugasan Indikator',
            'notifications' => 'Notifikasi',
            'permissions' => 'Permission',
            'quality-documents' => 'Dokumen Mutu',
            'quality-standards' => 'Standar Mutu',
            'reports' => 'Laporan',
            'roles' => 'Role',
            'spmi-periods' => 'Periode SPMI',
            'standard-categories' => 'Kategori Standar',
            'standard-indicators' => 'Indikator Standar',
            'units' => 'Unit',
            'users' => 'Pengguna',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function permissionActions(): array
    {
        return [
            'access' => 'Mengakses',
            'approve' => 'Menyetujui',
            'create' => 'Membuat',
            'delete' => 'Menghapus',
            'export' => 'Mengekspor',
            'finalize' => 'Memfinalisasi',
            'impersonate' => 'Masuk Sebagai',
            'review' => 'Mereview',
            'submit' => 'Mengirim',
            'update' => 'Mengubah',
            'view' => 'Melihat',
        ];
    }

    protected function forgetPermissionCache(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
