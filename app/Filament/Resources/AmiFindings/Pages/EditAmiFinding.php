<?php

namespace App\Filament\Resources\AmiFindings\Pages;

use App\Filament\Resources\AmiFindings\AmiFindingResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAmiFinding extends EditRecord
{
    protected static string $resource = AmiFindingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
