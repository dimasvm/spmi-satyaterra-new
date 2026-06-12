<?php

namespace App\Filament\Resources\AmiPeriods\Pages;

use App\Filament\Resources\AmiPeriods\AmiPeriodResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAmiPeriod extends EditRecord
{
    protected static string $resource = AmiPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
