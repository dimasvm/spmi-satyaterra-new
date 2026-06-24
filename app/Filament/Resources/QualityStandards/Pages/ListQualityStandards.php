<?php

namespace App\Filament\Resources\QualityStandards\Pages;

use App\Filament\Imports\QualityStandardImporter;
use App\Filament\Resources\QualityStandards\QualityStandardResource;
use App\Filament\Resources\QualityStandards\Widgets\QualityStandardOverview;
use App\Models\QualityStandard;
use App\Models\StandardIndicator;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;

class ListQualityStandards extends ListRecords
{
    protected static string $resource = QualityStandardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadImportTemplate')
                ->label('Template')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->color(Color::Zinc)
                ->url(asset('templates/template-impor-standar-mutu.xlsx'))
                ->openUrlInNewTab(),
            Action::make('importQualityStandards')
                ->label('Impor')
                ->icon(Heroicon::OutlinedArrowUpTray)
                ->modalHeading('Impor Standar Mutu dan Indikator')
                ->schema([
                    FileUpload::make('file')
                        ->label('File Excel')
                        ->acceptedFileTypes([
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ])
                        ->rules(['mimes:xls,xlsx'])
                        ->maxSize(5120)
                        ->storeFiles(false)
                        ->required(),
                ])
                ->visible(fn (): bool => (auth()->user()?->can('create', QualityStandard::class) ?? false)
                    && (auth()->user()?->can('create', StandardIndicator::class) ?? false))
                ->action(function (array $data): void {
                    /** @var TemporaryUploadedFile $file */
                    $file = $data['file'];
                    $importer = new QualityStandardImporter(auth()->user());

                    $readerType = str($file->getClientOriginalName())->lower()->endsWith('.xls')
                        ? ExcelFormat::XLS
                        : ExcelFormat::XLSX;

                    try {
                        Excel::import($importer, $file, null, $readerType);

                        Notification::make()
                            ->title('Impor standar mutu selesai')
                            ->body(sprintf(
                                'Standar: %d baru, %d diperbarui. Indikator: %d baru, %d diperbarui.',
                                $importer->getCreatedStandardsCount(),
                                $importer->getUpdatedStandardsCount(),
                                $importer->getCreatedIndicatorsCount(),
                                $importer->getUpdatedIndicatorsCount(),
                            ))
                            ->success()
                            ->send();
                    } catch (ValidationException $e) {
                        $errors = collect($e->errors())->flatten()->take(5)->implode('<br>• ');
                        Notification::make()
                            ->title('Validasi Impor Gagal')
                            ->body('Periksa kembali isian file Excel Anda. Beberapa data tidak valid:<br><br>• '.$errors)
                            ->danger()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Impor Gagal')
                            ->body('Terjadi kesalahan: '.$e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            CreateAction::make()->icon(Heroicon::Plus)->label('Tambah'),
            Action::make('manageStandardCategories')
                ->label('Kategori Standar')
                ->icon(Heroicon::OutlinedTag)
                ->color(Color::Zinc)
                ->modalHeading('Kategori Standar')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Tutup')
                ->modalContent(view('filament.resources.quality-standards.pages.manage-standard-categories')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            QualityStandardOverview::class,
        ];
    }
}
