<?php

namespace App\Filament\Resources\AmiChecklists\Pages;

use App\Filament\Resources\AmiChecklists\AmiChecklistResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAmiChecklists extends ListRecords
{
    protected static string $resource = AmiChecklistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
