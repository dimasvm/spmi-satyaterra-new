<?php

namespace App\Filament\Resources\AmiFindings\Tables;

use App\Enums\AmiFindingCategory;
use App\Enums\AmiFindingStatus;
use App\Filament\Resources\AmiFindings\AmiFindingResource;
use App\Models\AmiFinding;
use App\Models\AmiPeriod;
use App\Models\Unit;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AmiFindingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('finding_number')
                    ->label('Nomor')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('audit.auditeeUnit.name')
                    ->label('Unit Auditee')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->sortable(),
                TextColumn::make('standardIndicator.code')
                    ->label('Kode Indikator')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(60)
                    ->wrap()
                    ->searchable(),
                TextColumn::make('due_date')
                    ->label('Batas Waktu')
                    ->date('d M Y')
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('createdBy.name')
                    ->label('Dibuat Oleh')
                    ->placeholder('-')
                    ->searchable(),
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
                SelectFilter::make('unit')
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
                SelectFilter::make('category')
                    ->label('Kategori')
                    ->options(AmiFindingCategory::class),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(AmiFindingStatus::class),
                Filter::make('due_date')
                    ->label('Batas Waktu')
                    ->schema([
                        DatePicker::make('from')
                            ->label('Dari'),
                        DatePicker::make('until')
                            ->label('Sampai'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('due_date', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('due_date', '<=', $date))),
            ])
            ->emptyStateHeading('Belum ada temuan audit')
            ->emptyStateDescription('Temuan akan muncul setelah auditor mencatat hasil observasi, minor, mayor, atau OFI dari checklist.')
            ->emptyStateIcon(Heroicon::OutlinedExclamationTriangle)
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn (AmiFinding $record): bool => AmiFindingResource::canEdit($record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => AmiFindingResource::canDeleteAny()),
                ])
                    ->visible(fn (): bool => AmiFindingResource::canDeleteAny()),
            ]);
    }
}
