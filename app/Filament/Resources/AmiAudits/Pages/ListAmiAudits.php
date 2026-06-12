<?php

namespace App\Filament\Resources\AmiAudits\Pages;

use App\Filament\Resources\AmiAudits\AmiAuditResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAmiAudits extends ListRecords
{
    protected static string $resource = AmiAuditResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
