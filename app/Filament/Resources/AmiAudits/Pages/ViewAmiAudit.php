<?php

namespace App\Filament\Resources\AmiAudits\Pages;

use App\Filament\Resources\AmiAudits\AmiAuditResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewAmiAudit extends ViewRecord
{
    protected static string $resource = AmiAuditResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportPdf')
                ->label('Unduh PDF')
                ->icon(Heroicon::OutlinedDocumentArrowDown)
                ->color('danger')
                ->url(fn () => route('ami-audits.export-pdf', $this->record))
                ->openUrlInNewTab()
                ->visible(fn (): bool => auth()->user()?->can('ami-audits.export')),
            EditAction::make(),
        ];
    }
}
