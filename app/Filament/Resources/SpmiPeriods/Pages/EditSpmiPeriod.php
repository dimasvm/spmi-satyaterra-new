<?php

namespace App\Filament\Resources\SpmiPeriods\Pages;

use App\Filament\Resources\SpmiPeriods\SpmiPeriodResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSpmiPeriod extends EditRecord
{
    protected static string $resource = SpmiPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
