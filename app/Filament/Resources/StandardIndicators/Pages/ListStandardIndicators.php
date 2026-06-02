<?php

namespace App\Filament\Resources\StandardIndicators\Pages;

use App\Filament\Resources\StandardIndicators\StandardIndicatorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStandardIndicators extends ListRecords
{
    protected static string $resource = StandardIndicatorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
