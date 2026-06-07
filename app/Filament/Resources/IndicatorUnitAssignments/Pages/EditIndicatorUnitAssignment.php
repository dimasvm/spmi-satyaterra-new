<?php

namespace App\Filament\Resources\IndicatorUnitAssignments\Pages;

use App\Filament\Resources\IndicatorUnitAssignments\IndicatorUnitAssignmentResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditIndicatorUnitAssignment extends EditRecord
{
    protected static string $resource = IndicatorUnitAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
