<?php

namespace App\Filament\Resources\CorrectiveActions\Pages;

use App\Filament\Resources\CorrectiveActions\CorrectiveActionResource;
use App\Filament\Resources\CorrectiveActions\Tables\CorrectiveActionsTable;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCorrectiveAction extends ViewRecord
{
    protected static string $resource = CorrectiveActionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn (): bool => CorrectiveActionResource::canEdit($this->record)),
            CorrectiveActionsTable::submitVerificationAction(),
            CorrectiveActionsTable::acceptAction(),
            CorrectiveActionsTable::requestRevisionAction(),
        ];
    }
}
