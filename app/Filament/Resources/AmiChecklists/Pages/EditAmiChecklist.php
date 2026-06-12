<?php

namespace App\Filament\Resources\AmiChecklists\Pages;

use App\Filament\Resources\AmiChecklists\AmiChecklistResource;
use App\Filament\Resources\AmiChecklists\Pages\Concerns\InteractsWithAmiChecklistContext;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class EditAmiChecklist extends EditRecord
{
    use InteractsWithAmiChecklistContext;

    protected static string $resource = AmiChecklistResource::class;

    protected string $view = 'filament.resources.ami-checklists.pages.edit-ami-checklist';

    public function getTitle(): string|Htmlable
    {
        return 'Audit Checklist AMI';
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
            ViewAction::make()
                ->icon(Heroicon::OutlinedEye),
            DeleteAction::make()
                ->visible(fn (): bool => AmiChecklistResource::canManageChecklistSetup()),
        ];
    }
}
