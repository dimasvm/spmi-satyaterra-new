<?php

namespace App\Filament\Pages;

use App\Models\SystemSetting;
use BackedEnum;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class SpmiWorkflowSetting extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan';

    protected static ?int $navigationSort = 11;

    protected static ?string $navigationLabel = 'Alur Validasi SPMI';

    protected static ?string $title = 'Pengaturan Alur Validasi SPMI';

    protected string $view = 'filament.pages.spmi-workflow-setting';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->hasRole(['super_admin', 'admin_lpm']);
    }

    public function mount(): void
    {
        $this->form->fill([
            'achievement_validation_required' => (bool) SystemSetting::get('achievement_validation_required', true),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Validasi Capaian Indikator')
                    ->description('Tentukan apakah capaian indikator yang diisi oleh unit harus melewati tahap validasi oleh PIC Monitoring atau langsung tervalidasi.')
                    ->schema([
                        Toggle::make('achievement_validation_required')
                            ->label('Wajib Validasi Capaian')
                            ->helperText('Jika diaktifkan, capaian yang diinput oleh Unit akan berstatus "Menunggu Validasi" dan harus divalidasi oleh PIC Monitoring terlebih dahulu sebelum bisa diaudit. Jika dinonaktifkan, capaian akan langsung berstatus "Tervalidasi" dan siap diaudit.')
                            ->inline(false)
                            ->default(true),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        SystemSetting::set('achievement_validation_required', (bool) $state['achievement_validation_required']);

        Notification::make()
            ->success()
            ->title('Pengaturan alur validasi berhasil disimpan.')
            ->send();
    }
}
