<?php

namespace App\Filament\Resources\QualityStandards\Pages;

use App\Filament\Resources\QualityStandards\QualityStandardResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditQualityStandard extends EditRecord
{
    protected static string $resource = QualityStandardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
