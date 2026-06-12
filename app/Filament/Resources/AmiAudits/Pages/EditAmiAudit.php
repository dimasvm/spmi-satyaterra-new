<?php

namespace App\Filament\Resources\AmiAudits\Pages;

use App\Filament\Resources\AmiAudits\AmiAuditResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAmiAudit extends EditRecord
{
    protected static string $resource = AmiAuditResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
