<?php

namespace App\Filament\Resources\AmiPeriods\Pages;

use App\Filament\Resources\AmiPeriods\AmiPeriodResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAmiPeriod extends ViewRecord
{
    protected static string $resource = AmiPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
