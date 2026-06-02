<?php

namespace App\Filament\Resources\IndicatorUnitAssignments\Pages;

use App\Filament\Resources\IndicatorUnitAssignments\IndicatorUnitAssignmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListIndicatorUnitAssignments extends ListRecords
{
    protected static string $resource = IndicatorUnitAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
