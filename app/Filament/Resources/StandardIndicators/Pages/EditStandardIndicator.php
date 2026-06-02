<?php

namespace App\Filament\Resources\StandardIndicators\Pages;

use App\Filament\Resources\StandardIndicators\StandardIndicatorResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStandardIndicator extends EditRecord
{
    protected static string $resource = StandardIndicatorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
