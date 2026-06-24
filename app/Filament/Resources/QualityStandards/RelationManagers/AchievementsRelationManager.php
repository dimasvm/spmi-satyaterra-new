<?php

namespace App\Filament\Resources\QualityStandards\RelationManagers;

use App\Enums\AchievementReviewStatus;
use App\Enums\AchievementStatus;
use App\Enums\SubmissionStatus;
use App\Filament\Resources\IndicatorAchievements\IndicatorAchievementResource;
use App\Models\IndicatorAchievement;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class AchievementsRelationManager extends RelationManager
{
    protected static string $relationship = 'achievements';

    protected static bool $shouldSkipAuthorization = true;

    protected static ?string $title = 'Capaian';

    protected static ?string $modelLabel = 'Capaian Indikator';

    protected static ?string $pluralModelLabel = 'Capaian Indikator';

    public function form(Schema $schema): Schema
    {
        return $schema;
    }

    protected function makeTable(): Table
    {
        return $this->makeBaseTable()
            ->query(fn (): Builder => $this->getOwnerRecord()->achievements())
            ->queryStringIdentifier(Str::lcfirst(class_basename(static::class)))
            ->heading($this->getTableHeading() ?? static::getTitle($this->getOwnerRecord(), $this->getPageClass()));
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->modifyQueryUsing(fn (Builder $query): Builder => $this->scopeForCurrentUser($query)
                ->with([
                    'assignment.unit',
                    'assignment.standardIndicator',
                    'submittedBy',
                    'latestReview',
                ])
                ->withCount('evidences'))
            ->columns([
                TextColumn::make('assignment.unit.name')
                    ->label('Unit')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('assignment.standardIndicator.code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('assignment.standardIndicator.statement')
                    ->label('Pernyataan Indikator')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('target')
                    ->label('Target')
                    ->state(fn (IndicatorAchievement $record): string => trim(implode(' ', array_filter([
                        $record->assignment?->standardIndicator?->target_operator?->value,
                        $record->assignment?->standardIndicator?->target_value,
                        $record->assignment?->standardIndicator?->target_unit,
                    ]))) ?: '-'),
                TextColumn::make('realization_value')
                    ->label('Realisasi')
                    ->numeric()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('achievement_status')
                    ->label('Status Capaian')
                    ->badge(),
                TextColumn::make('submission_status')
                    ->label('Status Submit')
                    ->badge(),
                TextColumn::make('submittedBy.name')
                    ->label('Pengirim')
                    ->placeholder('-'),
                TextColumn::make('submitted_at')
                    ->label('Waktu Submit')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('evidences_count')
                    ->label('Bukti')
                    ->badge(),
                TextColumn::make('latestReview.status')
                    ->label('Review Terakhir')
                    ->badge()
                    ->placeholder('Belum direview'),
            ])
            ->filters([
                SelectFilter::make('unit')
                    ->label('Unit')
                    ->relationship('assignment.unit', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('achievement_status')
                    ->label('Status Capaian')
                    ->options(AchievementStatus::class),
                SelectFilter::make('submission_status')
                    ->label('Status Submit')
                    ->options(SubmissionStatus::class),
                Filter::make('submitted_at')
                    ->schema([
                        DatePicker::make('submitted_from')
                            ->label('Dari Tanggal'),
                        DatePicker::make('submitted_until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['submitted_from'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('submitted_at', '>=', $date))
                        ->when($data['submitted_until'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('submitted_at', '<=', $date))),
                SelectFilter::make('review_status')
                    ->label('Status Review')
                    ->options([
                        AchievementReviewStatus::Validated->value => AchievementReviewStatus::Validated->getLabel(),
                        AchievementReviewStatus::Returned->value => AchievementReviewStatus::Returned->getLabel(),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => filled($data['value'] ?? null)
                        ? $query->whereHas('latestReview', fn (Builder $reviewQuery): Builder => $reviewQuery->where('status', $data['value']))
                        : $query),
            ])
            ->recordActions([
                Action::make('detail')
                    ->label('Detail')
                    ->icon(Heroicon::Eye)
                    ->color('gray')
                    ->url(fn (IndicatorAchievement $record): string => IndicatorAchievementResource::getUrl('view', ['record' => $record])),
            ]);
    }

    private function scopeForCurrentUser(Builder $query): Builder
    {
        $user = auth()->user();

        if ($user === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->forUser($user);
    }
}
