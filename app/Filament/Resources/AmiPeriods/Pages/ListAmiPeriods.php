<?php

namespace App\Filament\Resources\AmiPeriods\Pages;

use App\Filament\Resources\AmiPeriods\AmiPeriodResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAmiPeriods extends ListRecords
{
    protected static string $resource = AmiPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
