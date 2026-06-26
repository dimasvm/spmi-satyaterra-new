<?php

namespace App\Filament\Pages;

use App\Models\CampusProfile;
use App\Services\PddiktiService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use UnitEnum;

class KampusSetting extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;

    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan';

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationLabel = 'Profil Kampus';

    protected static ?string $title = 'Pengaturan Profil Kampus';

    protected string $view = 'filament.pages.kampus-setting';

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->hasRole(['super_admin', 'admin_lpm']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(CampusProfile::query()->orderByDesc('is_active')->orderByDesc('last_synced_at'))
            ->columns([
                TextColumn::make('name')
                    ->label('Nama PT')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (CampusProfile $record): string => $record->type.' — '.$record->city),

                TextColumn::make('accreditation')
                    ->label('Akreditasi')
                    ->badge()
                    ->color(fn (CampusProfile $record): string => match (strtolower($record->accreditation ?? '')) {
                        'unggul', 'a' => 'success',
                        'baik sekali', 'b' => 'info',
                        'baik', 'c' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('total_students')
                    ->label('Mahasiswa')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('total_study_programs')
                    ->label('Program Studi')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif di Halaman Publik' : 'Tidak Aktif')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray'),

                TextColumn::make('last_synced_at')
                    ->label('Terakhir Sync')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->placeholder('Belum pernah sync'),
            ])
            ->emptyStateHeading('Belum ada kampus')
            ->emptyStateDescription('Tambahkan kampus dari PDDikti untuk ditampilkan di halaman publik.')
            ->emptyStateIcon(Heroicon::OutlinedBuildingOffice)
            ->headerActions([
                Action::make('addCampus')
                    ->label('Tambah Kampus dari PDDikti')
                    ->icon(Heroicon::Plus)
                    ->color('primary')
                    ->modalHeading('Cari & Tambah Kampus dari PDDikti')
                    ->modalDescription('Ketik nama kampus, lalu pilih dari daftar drop-down.')
                    ->modalWidth('md')
                    ->schema([
                        Select::make('pddikti_id')
                            ->label('Pilih Perguruan Tinggi')
                            ->placeholder('Ketik min. 3 huruf...')
                            ->searchable()
                            ->getSearchResultsUsing(function (string $search) {
                                if (strlen($search) < 3) {
                                    return [];
                                }
                                $results = app(PddiktiService::class)->searchUniversity($search);

                                return collect($results)
                                    ->mapWithKeys(fn ($item) => [$item['id'] => $item['name'].' ('.($item['npsn'] ?? '-').')'])
                                    ->toArray();
                            })
                            ->getOptionLabelUsing(function ($value) {
                                $detail = app(PddiktiService::class)->getUniversityDetail($value);

                                return $detail['nama_pt'] ?? $detail['nama'] ?? 'Kampus Terpilih';
                            })
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        $pddiktiId = $data['pddikti_id'];

                        $detail = app(PddiktiService::class)->getUniversityDetail($pddiktiId);

                        if (empty($detail)) {
                            Notification::make()
                                ->danger()
                                ->title('Gagal mendapatkan detail kampus')
                                ->body('Silakan coba kembali dalam beberapa saat.')
                                ->send();

                            return;
                        }

                        CampusProfile::firstOrCreate(
                            ['pddikti_id' => $pddiktiId],
                            [
                                'name' => $detail['nama_pt'] ?? $detail['nama'] ?? 'Nama Tidak Diketahui',
                                'npsn' => $detail['kode_pt'] ?? null,
                                'type' => $detail['jenis_pt'] ?? $detail['bentuk_pt'] ?? null,
                                'status' => $detail['stat_pt'] ?? $detail['status'] ?? 'Aktif',
                                'is_active' => false,
                            ]
                        );

                        Notification::make()
                            ->success()
                            ->title('Kampus berhasil ditambahkan')
                            ->body('Klik "Sync Data" untuk mengambil data lengkap dari PDDikti.')
                            ->send();
                    }),
            ])
            ->recordActions([
                Action::make('activate')
                    ->label('Aktifkan')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Aktifkan kampus ini?')
                    ->modalDescription('Kampus ini akan ditampilkan di halaman publik aplikasi.')
                    ->visible(fn (CampusProfile $record): bool => ! $record->is_active)
                    ->action(function (CampusProfile $record): void {
                        $record->activate();

                        Notification::make()
                            ->success()
                            ->title('Kampus diaktifkan')
                            ->body($record->name.' sekarang ditampilkan di halaman publik.')
                            ->send();
                    }),

                Action::make('syncData')
                    ->label('Sync Data')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Sync data dari PDDikti?')
                    ->modalDescription('Data kampus akan diperbarui dari PDDikti. Proses ini membutuhkan beberapa saat.')
                    ->action(function (CampusProfile $record): void {
                        $service = app(PddiktiService::class);
                        $success = $service->syncCampusProfile($record);

                        if ($success) {
                            Notification::make()
                                ->success()
                                ->title('Data berhasil disinkronisasi')
                                ->body('Data kampus '.$record->name.' telah diperbarui dari PDDikti.')
                                ->send();
                        } else {
                            Notification::make()
                                ->danger()
                                ->title('Gagal sync data')
                                ->body('Tidak dapat mengambil data dari PDDikti. Pastikan koneksi internet tersedia dan coba lagi.')
                                ->send();
                        }
                    }),
            ]);
    }
}
