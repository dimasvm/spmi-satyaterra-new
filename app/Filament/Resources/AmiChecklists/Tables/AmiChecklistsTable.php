<?php

namespace App\Filament\Resources\AmiChecklists\Tables;

use App\Enums\AmiAssessmentResult;
use App\Filament\Resources\AmiChecklists\AmiChecklistResource;
use App\Models\AmiChecklist;
use App\Models\AmiPeriod;
use App\Models\Unit;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AmiChecklistsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('audit.amiPeriod.name')
                    ->label('Periode AMI')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('audit.auditeeUnit.name')
                    ->label('Unit Auditee')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('standardIndicator.code')
                    ->label('Kode')
                    ->searchable(),
                TextColumn::make('standardIndicator.statement')
                    ->label('Indikator')
                    ->limit(70)
                    ->wrap()
                    ->searchable(),
                TextColumn::make('assessment_result')
                    ->label('Hasil Audit')
                    ->badge()
                    ->placeholder('Belum dinilai'),
                TextColumn::make('updated_at')
                    ->label('Update')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('ami_period')
                    ->label('Periode AMI')
                    ->options(fn (): array => AmiPeriod::query()
                        ->orderByDesc('start_date')
                        ->pluck('name', 'id')
                        ->all())
                    ->query(fn (Builder $query, array $data): Builder => filled($data['value'] ?? null)
                        ? $query->whereHas('audit', fn (Builder $auditQuery): Builder => $auditQuery->where('ami_period_id', $data['value']))
                        : $query)
                    ->searchable()
                    ->preload(),
                SelectFilter::make('auditee_unit')
                    ->label('Unit Auditee')
                    ->options(fn (): array => Unit::query()
                        ->active()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->query(fn (Builder $query, array $data): Builder => filled($data['value'] ?? null)
                        ? $query->whereHas('audit', fn (Builder $auditQuery): Builder => $auditQuery->where('auditee_unit_id', $data['value']))
                        : $query)
                    ->searchable()
                    ->preload(),
                SelectFilter::make('assessment_result')
                    ->label('Hasil Audit')
                    ->options(AmiAssessmentResult::class),
                Filter::make('assigned_to_me')
                    ->label('Ditugaskan ke saya')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->whereHas('audit.auditorAssignments', fn (Builder $auditorQuery): Builder => $auditorQuery
                        ->where('user_id', auth()->id()))),
            ])
            ->emptyStateHeading('Belum ada checklist audit')
            ->emptyStateDescription('Generate checklist dari jadwal audit agar auditor dapat mulai mengisi hasil AMI.')
            ->emptyStateIcon(Heroicon::OutlinedClipboardDocumentList)
            ->defaultSort('updated_at', 'desc')
            ->recordActions([
                Action::make('audit')
                    ->label('Isi Checklist')
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->color('primary')
                    ->visible(fn (AmiChecklist $record): bool => AmiChecklistResource::canEdit($record))
                    ->url(fn (AmiChecklist $record): string => AmiChecklistResource::getUrl('edit', [
                        'record' => $record,
                    ])),
                AmiChecklistResource::createFindingAction(),
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn (AmiChecklist $record): bool => AmiChecklistResource::canEdit($record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => AmiChecklistResource::canManageChecklistSetup()),
                ])
                    ->visible(fn (): bool => AmiChecklistResource::canManageChecklistSetup()),
            ]);
    }
}
