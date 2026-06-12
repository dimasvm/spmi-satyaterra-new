<?php

namespace App\Filament\Pages;

use App\Enums\ReportType;
use App\Models\AmiPeriod;
use App\Models\SpmiPeriod;
use App\Models\StandardCategory;
use App\Models\Unit;
use App\Services\Reports\ReportQueryService;
use BackedEnum;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Maatwebsite\Excel\Facades\Excel;
use UnitEnum;

class ReportsPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBarSquare;

    protected static string|UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Laporan';

    protected static ?string $title = 'Laporan & Export';

    protected string $view = 'filament.pages.reports-page';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    /**
     * @var array<int, string>
     */
    public array $headings = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $previewRows = [];

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->can('reports.view');
    }

    public function mount(): void
    {
        $this->form->fill([
            'jenis_laporan' => ReportType::IndicatorByPeriod->value,
        ]);

        $this->refreshPreview();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Filter Laporan')
                    ->description('Pilih jenis laporan dan batasi data sesuai periode, unit, standar, status, atau rentang tanggal.')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'md' => 2,
                            'xl' => 4,
                        ])
                            ->schema([
                                Select::make('jenis_laporan')
                                    ->label('Jenis Laporan')
                                    ->options($this->reportTypeOptions())
                                    ->default(ReportType::IndicatorByPeriod->value)
                                    ->required()
                                    ->native(false),
                                Select::make('spmi_period_id')
                                    ->label('Periode SPMI')
                                    ->options(fn (): array => SpmiPeriod::query()->orderByDesc('start_date')->pluck('name', 'id')->all())
                                    ->searchable()
                                    ->preload(),
                                Select::make('ami_period_id')
                                    ->label('Periode AMI')
                                    ->options(fn (): array => AmiPeriod::query()->orderByDesc('start_date')->pluck('name', 'id')->all())
                                    ->searchable()
                                    ->preload(),
                                Select::make('unit_id')
                                    ->label('Unit')
                                    ->options(fn (): array => $this->unitOptions())
                                    ->searchable()
                                    ->preload(),
                                Select::make('standard_category_id')
                                    ->label('Kategori Standar')
                                    ->options(fn (): array => StandardCategory::query()->orderBy('name')->pluck('name', 'id')->all())
                                    ->searchable()
                                    ->preload(),
                                Select::make('status')
                                    ->label('Status')
                                    ->options($this->statusOptions())
                                    ->searchable()
                                    ->native(false),
                                DatePicker::make('date_from')
                                    ->label('Tanggal Dari'),
                                DatePicker::make('date_until')
                                    ->label('Tanggal Sampai'),
                            ]),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function refreshPreview(): void
    {
        $type = $this->reportType();
        $reports = app(ReportQueryService::class);

        $this->headings = $reports->headings($type);
        $this->previewRows = $reports->rows($type, $this->filters())
            ->take(15)
            ->values()
            ->all();
    }

    public function exportPdf()
    {
        abort_unless($this->canExport(), 403);

        if (! class_exists(Pdf::class)) {
            $this->missingPackageNotification('PDF', 'composer require barryvdh/laravel-dompdf');

            return null;
        }

        return redirect()->route('reports.export', $this->exportParameters('pdf'));
    }

    public function exportExcel()
    {
        abort_unless($this->canExport(), 403);

        if (! class_exists(Excel::class)) {
            $this->missingPackageNotification('Excel', 'composer require maatwebsite/excel');

            return null;
        }

        return redirect()->route('reports.export', $this->exportParameters('excel'));
    }

    /**
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('Tampilkan Preview')
                ->icon(Heroicon::OutlinedMagnifyingGlass)
                ->color('gray')
                ->action(fn (): null => $this->refreshPreview()),
            Action::make('pdf')
                ->label('Generate PDF')
                ->icon(Heroicon::OutlinedDocumentArrowDown)
                ->color('danger')
                ->visible(fn (): bool => $this->canExport())
                ->action(fn () => $this->exportPdf()),
            Action::make('excel')
                ->label('Export Excel')
                ->icon(Heroicon::OutlinedTableCells)
                ->color('success')
                ->visible(fn (): bool => $this->canExport())
                ->action(fn () => $this->exportExcel()),
        ];
    }

    /**
     * @return array<int|string, string>
     */
    private function unitOptions(): array
    {
        $user = auth()->user();

        if ($user?->hasRole('unit_pic') && $user->unit_id !== null) {
            return Unit::query()->whereKey($user->unit_id)->pluck('name', 'id')->all();
        }

        return Unit::query()->active()->orderBy('name')->pluck('name', 'id')->all();
    }

    /**
     * @return array<string, string>
     */
    private function statusOptions(): array
    {
        return [
            'draft' => 'Draft',
            'submitted' => 'Dikirim',
            'validated' => 'Tervalidasi',
            'returned' => 'Dikembalikan',
            'planned' => 'Direncanakan',
            'ongoing' => 'Berjalan',
            'finalized' => 'Final',
            'open' => 'Terbuka',
            'waiting_verification' => 'Menunggu Verifikasi',
            'need_revision' => 'Perlu Revisi',
            'closed' => 'Ditutup',
            'accepted' => 'Diterima',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function filters(): array
    {
        return collect($this->form->getState())
            ->filter(fn (mixed $value): bool => filled($value))
            ->all();
    }

    private function reportType(): ReportType
    {
        $state = $this->form->getState()['jenis_laporan'] ?? null;

        if ($state instanceof ReportType) {
            return $state;
        }

        return ReportType::tryFrom((string) $state) ?? ReportType::IndicatorByPeriod;
    }

    /**
     * @return array<string, mixed>
     */
    private function exportParameters(string $format): array
    {
        $filters = collect($this->filters())
            ->map(fn (mixed $value): mixed => $value instanceof ReportType ? $value->value : $value)
            ->all();

        $filters['jenis_laporan'] = $this->reportType()->value;
        $filters['format'] = $format;

        return $filters;
    }

    private function canExport(): bool
    {
        return (bool) auth()->user()?->can('reports.export');
    }

    /**
     * @return array<string, string>
     */
    private function reportTypeOptions(): array
    {
        return collect(ReportType::cases())
            ->mapWithKeys(fn (ReportType $type): array => [$type->value => $type->getLabel()])
            ->all();
    }

    private function missingPackageNotification(string $format, string $command): void
    {
        Notification::make()
            ->warning()
            ->title("Package {$format} belum tersedia.")
            ->body("Jalankan: {$command}")
            ->persistent()
            ->send();
    }
}
