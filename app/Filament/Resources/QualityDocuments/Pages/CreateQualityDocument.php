<?php

namespace App\Filament\Resources\QualityDocuments\Pages;

use App\Enums\QualityDocumentStatus;
use App\Filament\Resources\QualityDocuments\QualityDocumentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateQualityDocument extends CreateRecord
{
    protected static string $resource = QualityDocumentResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['uploaded_by'] = auth()->id();
        $data['status'] ??= QualityDocumentStatus::Draft;

        return $data;
    }
}
