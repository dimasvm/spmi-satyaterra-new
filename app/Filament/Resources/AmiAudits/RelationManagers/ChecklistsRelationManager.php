<?php

namespace App\Filament\Resources\AmiAudits\RelationManagers;

use App\Filament\Resources\AmiChecklists\AmiChecklistResource;
use App\Models\AmiChecklist;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ChecklistsRelationManager extends RelationManager
{
    protected static string $relationship = 'checklists';

    protected static ?string $title = 'Checklist Audit';

    protected static ?string $modelLabel = 'Checklist Audit';

    protected static ?string $pluralModelLabel = 'Checklist Audit';

    public function form(Schema $schema): Schema
    {
        return AmiChecklistResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['standardIndicator.qualityStandard', 'audit.auditorAssignments']))
            ->recordTitleAttribute('standardIndicator.code')
            ->columns([
                TextColumn::make('standardIndicator.code')
                    ->label('Kode')
                    ->searchable(),
                TextColumn::make('standardIndicator.statement')
                    ->label('Indikator')
                    ->limit(80)
                    ->wrap()
                    ->searchable(),
                TextColumn::make('standardIndicator.qualityStandard.name')
                    ->label('Standar')
                    ->limit(45)
                    ->wrap(),
                TextColumn::make('assessment_result')
                    ->label('Hasil Audit')
                    ->badge()
                    ->placeholder('Belum dinilai'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->visible(fn (): bool => AmiChecklistResource::canManageChecklistSetup()),
            ])
            ->recordActions([
                Action::make('audit')
                    ->label('Isi Checklist')
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->color('primary')
                    ->visible(fn (AmiChecklist $record): bool => AmiChecklistResource::canEdit($record))
                    ->url(fn (AmiChecklist $record): string => AmiChecklistResource::getUrl('edit', [
                        'record' => $record,
                    ])),
                Action::make('view')
                    ->label('Detail')
                    ->icon(Heroicon::OutlinedEye)
                    ->url(fn (AmiChecklist $record): string => AmiChecklistResource::getUrl('view', [
                        'record' => $record,
                    ])),
                AmiChecklistResource::createFindingAction(),
                EditAction::make()
                    ->visible(fn (AmiChecklist $record): bool => AmiChecklistResource::canEdit($record)),
                DeleteAction::make()
                    ->visible(fn (): bool => AmiChecklistResource::canManageChecklistSetup()),
            ]);
    }
}
