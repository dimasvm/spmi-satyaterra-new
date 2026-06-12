<?php

namespace App\Filament\Resources\QualityDocuments\Pages;

use App\Filament\Resources\QualityDocuments\QualityDocumentResource;
use App\Filament\Resources\QualityDocuments\Tables\QualityDocumentsTable;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewQualityDocument extends ViewRecord
{
    protected static string $resource = QualityDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            QualityDocumentsTable::openDocumentAction(),
            EditAction::make()
                ->visible(fn (): bool => QualityDocumentResource::canEdit($this->record)),
            QualityDocumentsTable::approveAction(),
            QualityDocumentsTable::archiveAction(),
        ];
    }
}
