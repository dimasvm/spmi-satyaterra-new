<?php

namespace App\Filament\Resources\AmiChecklists\Pages;

use App\Filament\Resources\AmiChecklists\AmiChecklistResource;
use App\Filament\Resources\AmiChecklists\Pages\Concerns\InteractsWithAmiChecklistContext;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class ViewAmiChecklist extends ViewRecord
{
    use InteractsWithAmiChecklistContext;

    protected static string $resource = AmiChecklistResource::class;

    protected string $view = 'filament.resources.ami-checklists.pages.view-ami-checklist';

    public function getTitle(): string|Htmlable
    {
        return 'Detail Checklist AMI';
    }

    public function getSubheading(): string|Htmlable|null
    {
        $record = $this->getRecord();

        return trim(implode(' - ', array_filter([
            $record->audit?->auditeeUnit?->name,
            $record->standardIndicator?->code,
        ]))) ?: null;
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Isi Assessment')
                ->icon(Heroicon::OutlinedPencilSquare)
                ->visible(fn (): bool => $this->canAssessChecklist()),
        ];
    }
}
