<?php

namespace App\Filament\Resources\AmiFindings\Pages;

use App\Filament\Resources\AmiFindings\AmiFindingResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAmiFinding extends ViewRecord
{
    protected static string $resource = AmiFindingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
