<?php

namespace App\Filament\Resources\IndicatorUnitAssignments\Pages;

use App\Filament\Resources\IndicatorUnitAssignments\IndicatorUnitAssignmentResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewIndicatorUnitAssignment extends ViewRecord
{
    protected static string $resource = IndicatorUnitAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
