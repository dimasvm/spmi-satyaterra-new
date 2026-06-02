<?php

namespace App\Filament\Resources\SpmiPeriods\Pages;

use App\Filament\Resources\SpmiPeriods\SpmiPeriodResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSpmiPeriods extends ListRecords
{
    protected static string $resource = SpmiPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
