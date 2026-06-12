<?php

namespace App\Filament\Resources\QualityDocuments\Pages;

use App\Filament\Resources\QualityDocuments\QualityDocumentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListQualityDocuments extends ListRecords
{
    protected static string $resource = QualityDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn (): bool => QualityDocumentResource::canCreate()),
        ];
    }
}
