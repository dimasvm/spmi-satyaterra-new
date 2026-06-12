<?php

namespace App\Filament\Resources\QualityDocuments\Pages;

use App\Filament\Resources\QualityDocuments\QualityDocumentResource;
use App\Filament\Resources\QualityDocuments\Tables\QualityDocumentsTable;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditQualityDocument extends EditRecord
{
    protected static string $resource = QualityDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            QualityDocumentsTable::openDocumentAction(),
            QualityDocumentsTable::approveAction(),
            QualityDocumentsTable::archiveAction(),
            DeleteAction::make()
                ->visible(fn (): bool => QualityDocumentResource::canDelete($this->record)),
        ];
    }
}
