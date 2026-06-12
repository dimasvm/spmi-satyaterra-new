<?php

namespace App\Filament\Resources\AmiAudits\Pages;

use App\Filament\Resources\AmiAudits\AmiAuditResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAmiAudit extends ViewRecord
{
    protected static string $resource = AmiAuditResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
