<?php

namespace App\Filament\Resources\QualityStandards\Pages;

use App\Filament\Resources\QualityStandards\QualityStandardResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewQualityStandard extends ViewRecord
{
    protected static string $resource = QualityStandardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
