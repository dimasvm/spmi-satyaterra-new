<?php

namespace App\Filament\Resources\QualityStandards\Pages;

use App\Filament\Resources\QualityStandards\QualityStandardResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListQualityStandards extends ListRecords
{
    protected static string $resource = QualityStandardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
