<?php

namespace App\Filament\Resources\QualityStandards\Pages;

use App\Filament\Resources\QualityStandards\QualityStandardResource;
use App\Filament\Resources\QualityStandards\Widgets\QualityStandardOverview;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;

class ListQualityStandards extends ListRecords
{
    protected static string $resource = QualityStandardResource::class;

    protected function getHeaderActions(): array
    {
        return [
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
