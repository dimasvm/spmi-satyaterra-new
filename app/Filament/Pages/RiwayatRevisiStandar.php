<?php

namespace App\Filament\Pages;

use App\Enums\StandardRevisionType;
use App\Models\SpmiPeriod;
use App\Models\StandardRevisionHistory;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class RiwayatRevisiStandar extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static string|UnitEnum|null $navigationGroup = 'Peningkatan';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Riwayat Revisi Standar';

    protected static ?string $title = 'Riwayat Revisi Standar';

    protected static ?string $slug = 'riwayat-revisi-standar';

    protected string $view = 'filament.pages.riwayat-revisi-standar';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return (bool) ($user?->isSuperAdmin() || $user?->isAdminLpm() || $user?->isPimpinan());
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(StandardRevisionHistory::query()
                ->with([
                    'qualityStandard.spmiPeriod',
                    'standardIndicator',
                    'standardImprovementProposal',
                    'revisedBy',
                ]))
            ->columns([
                TextColumn::make('revised_at')
                    ->label('Tanggal Revisi')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('revision_type')
                    ->label('Jenis Revisi')
                    ->badge()
                    ->sortable(),
                TextColumn::make('qualityStandard.name')
                    ->label('Standar')
                    ->placeholder('-')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('standardIndicator.code')
                    ->label('Indikator')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('standardImprovementProposal.title')
                    ->label('Usulan Asal')
                    ->placeholder('-')
                    ->searchable()
                    ->limit(45),
                TextColumn::make('revisedBy.name')
                    ->label('Direvisi Oleh')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('notes')
                    ->label('Catatan')
                    ->placeholder('-')
                    ->limit(60)
                    ->wrap(),
            ])
            ->filters([
                SelectFilter::make('revision_type')
                    ->label('Jenis Revisi')
                    ->options(StandardRevisionType::class),
                SelectFilter::make('quality_standard_id')
                    ->label('Standar')
                    ->relationship('qualityStandard', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('spmi_period_id')
                    ->label('Periode SPMI')
                    ->options(fn (): array => SpmiPeriod::query()
                        ->orderByDesc('start_date')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->preload()
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['value'] ?? null, fn (Builder $query, mixed $periodId): Builder => $query
                            ->whereHas('qualityStandard', fn (Builder $standardQuery): Builder => $standardQuery
                                ->where('spmi_period_id', $periodId)))),
            ])
            ->defaultSort('revised_at', 'desc');
    }

    /**
     * @return array<string, int>
     */
    public function stats(): array
    {
        $query = StandardRevisionHistory::query();

        return [
            'total' => (clone $query)->count(),
            'standard' => (clone $query)->where('revision_type', StandardRevisionType::StandardRevision->value)->count(),
            'indicator' => (clone $query)->where('revision_type', StandardRevisionType::IndicatorRevision->value)->count(),
            'target' => (clone $query)->where('revision_type', StandardRevisionType::TargetRevision->value)->count(),
        ];
    }
}
