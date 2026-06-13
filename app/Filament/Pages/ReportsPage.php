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

    protected static ?string $navigationLabel = 'Pusat Laporan';

    protected static ?string $title = 'Pusat Laporan';

    protected static ?string $slug = 'pusat-laporan';

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
            'jenis_laporan' => $this->defaultReportType()->value,
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
            ->take(10)
            ->values()
            ->all();
    }

    public function selectReport(string $reportType): void
    {
        $type = ReportType::tryFrom($reportType);

        if ($type === null || ! $this->canViewReportType($type)) {
            abort(403);
        }

        $this->setReportType($type);
        $this->refreshPreview();
    }

    public function exportPdf(?string $reportType = null)
    {
        abort_unless($this->canExport(), 403);

        $this->setReportTypeFromRequest($reportType);

        if (! class_exists(Pdf::class)) {
            $this->missingPackageNotification('PDF', 'composer require barryvdh/laravel-dompdf');

            return null;
        }

        return redirect()->route('reports.export', $this->exportParameters('pdf'));
    }

    public function exportExcel(?string $reportType = null)
    {
        abort_unless($this->canExport(), 403);

        $this->setReportTypeFromRequest($reportType);

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
     * @return array<int, array<string, mixed>>
     */
    public function reportCards(): array
    {
        return collect([
            [
                'type' => ReportType::IndicatorByPeriod,
                'title' => 'Laporan Capaian Indikator',
                'description' => 'Rekap realisasi indikator, target, status capaian, validasi, dan jumlah bukti pendukung.',
                'filters' => ['Periode SPMI', 'Unit', 'Status', 'Rentang tanggal'],
                'icon' => Heroicon::OutlinedChartBarSquare,
            ],
            [
                'type' => ReportType::LpmValidation,
                'title' => 'Laporan Validasi Capaian',
                'description' => 'Daftar capaian yang sudah masuk alur validasi LPM beserta catatan review terakhir.',
                'filters' => ['Periode SPMI', 'Unit', 'Kategori standar', 'Status'],
                'icon' => Heroicon::OutlinedClipboardDocumentCheck,
            ],
            [
                'type' => ReportType::AmiByPeriod,
                'title' => 'Laporan AMI',
                'description' => 'Ringkasan audit per periode AMI, unit auditee, auditor, checklist, dan status audit.',
                'filters' => ['Periode AMI', 'Unit', 'Status audit', 'Tanggal audit'],
                'icon' => Heroicon::OutlinedClipboardDocumentList,
            ],
            [
                'type' => ReportType::AuditFindings,
                'title' => 'Laporan Temuan Audit',
                'description' => 'Temuan AMI, kategori, rekomendasi, deadline, dan status tindak lanjut awal.',
                'filters' => ['Periode AMI', 'Unit', 'Kategori standar', 'Status'],
                'icon' => Heroicon::OutlinedExclamationTriangle,
            ],
            [
                'type' => ReportType::CorrectiveActions,
                'title' => 'Laporan Tindak Lanjut',
                'description' => 'Rencana tindakan, PIC, target selesai, status pengerjaan, dan hasil verifikasi.',
                'filters' => ['Periode AMI', 'Unit', 'Status', 'Target selesai'],
                'icon' => Heroicon::OutlinedArrowPathRoundedSquare,
            ],
            [
                'type' => ReportType::ManagementReviews,
                'title' => 'Laporan RTM',
                'description' => 'Rapat tinjauan manajemen, periode terkait, jumlah keputusan, usulan peningkatan, dan status.',
                'filters' => ['Periode SPMI', 'Periode AMI', 'Status RTM', 'Tanggal rapat'],
                'icon' => Heroicon::OutlinedRectangleGroup,
            ],
            [
                'type' => ReportType::StandardImprovements,
                'title' => 'Laporan Peningkatan Standar',
                'description' => 'Usulan peningkatan standar dari RTM, jenis perubahan, status review, dan periode target.',
                'filters' => ['Periode SPMI', 'Periode AMI', 'Kategori standar', 'Status'],
                'icon' => Heroicon::OutlinedSparkles,
            ],
        ])
            ->filter(fn (array $card): bool => $this->canViewReportType($card['type']))
            ->map(fn (array $card): array => [
                ...$card,
                'type_value' => $card['type']->value,
                'is_active' => $this->reportType() === $card['type'],
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function activeReportCard(): array
    {
        return collect($this->reportCards())
            ->firstWhere('type_value', $this->reportType()->value)
            ?? $this->reportCards()[0]
            ?? [];
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
            'draft' => 'Draf',
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
            'scheduled' => 'Terjadwal',
            'completed' => 'Selesai',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'implemented' => 'Diimplementasikan',
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

        $type = ReportType::tryFrom((string) $state) ?? $this->defaultReportType();

        if (! $this->canViewReportType($type)) {
            return $this->defaultReportType();
        }

        return $type;
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
            ->filter(fn (ReportType $type): bool => $this->canViewReportType($type))
            ->mapWithKeys(fn (ReportType $type): array => [$type->value => $type->getLabel()])
            ->all();
    }

    private function defaultReportType(): ReportType
    {
        return collect(ReportType::cases())
            ->first(fn (ReportType $type): bool => $this->canViewReportType($type))
            ?? ReportType::IndicatorByPeriod;
    }

    private function setReportTypeFromRequest(?string $reportType): void
    {
        if ($reportType === null) {
            return;
        }

        $type = ReportType::tryFrom($reportType);

        abort_unless($type !== null && $this->canViewReportType($type), 403);

        $this->setReportType($type);
        $this->refreshPreview();
    }

    private function setReportType(ReportType $type): void
    {
        $state = $this->form->getState();
        $state['jenis_laporan'] = $type->value;

        $this->form->fill($state);
    }

    private function canViewReportType(ReportType $type): bool
    {
        $user = auth()->user();

        if ($user?->hasAnyRole(['super_admin', 'admin_lpm', 'pimpinan'])) {
            return true;
        }

        if ($user?->hasRole('unit_pic')) {
            return in_array($type, [
                ReportType::IndicatorByPeriod,
                ReportType::AuditFindings,
                ReportType::CorrectiveActions,
                ReportType::ManagementReviews,
                ReportType::StandardImprovements,
            ], true);
        }

        if ($user?->hasRole('auditor')) {
            return in_array($type, [
                ReportType::AmiByPeriod,
                ReportType::AuditFindings,
                ReportType::CorrectiveActions,
                ReportType::ManagementReviews,
                ReportType::StandardImprovements,
            ], true);
        }

        if ($user?->hasRole('viewer')) {
            return in_array($type, [
                ReportType::IndicatorByPeriod,
                ReportType::AmiByPeriod,
                ReportType::AuditFindings,
                ReportType::ManagementReviews,
                ReportType::StandardImprovements,
            ], true);
        }

        return false;
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
