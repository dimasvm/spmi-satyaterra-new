<?php

namespace App\Filament\Resources\CorrectiveActions\Pages;

use App\Filament\Resources\CorrectiveActions\CorrectiveActionResource;
use App\Filament\Resources\CorrectiveActions\Tables\CorrectiveActionsTable;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCorrectiveAction extends EditRecord
{
    protected static string $resource = CorrectiveActionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            CorrectiveActionsTable::submitVerificationAction(),
            CorrectiveActionsTable::acceptAction(),
            CorrectiveActionsTable::requestRevisionAction(),
            DeleteAction::make()
                ->visible(fn (): bool => CorrectiveActionResource::canDelete($this->record)),
        ];
    }
}
