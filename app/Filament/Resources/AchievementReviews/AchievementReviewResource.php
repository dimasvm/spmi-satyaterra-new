<?php

namespace App\Filament\Resources\AchievementReviews;

use App\Enums\SubmissionStatus;
use App\Filament\Resources\AchievementReviews\Pages\ListAchievementReviews;
use App\Filament\Resources\AchievementReviews\Pages\ViewAchievementReview;
use App\Filament\Resources\AchievementReviews\Schemas\AchievementReviewForm;
use App\Filament\Resources\AchievementReviews\Schemas\AchievementReviewInfolist;
use App\Filament\Resources\AchievementReviews\Tables\AchievementReviewsTable;
use App\Models\AchievementReview;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class AchievementReviewResource extends Resource
{
    protected static ?string $model = AchievementReview::class;

    protected static ?string $modelLabel = 'Validasi Capaian';

    protected static ?string $pluralModelLabel = 'Validasi Capaian';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static string|UnitEnum|null $navigationGroup = 'SPMI';

    protected static ?int $navigationSort = 6;

    public static function getNavigationLabel(): string
    {
        return 'Validasi Capaian';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with([
                'achievement' => fn ($achievementQuery) => $achievementQuery->withCount('evidences'),
                'achievement.assignment.spmiPeriod',
                'achievement.assignment.standardIndicator.qualityStandard',
                'achievement.assignment.unit',
                'achievement.evidences.uploadedBy',
                'achievement.reviews.reviewer',
                'achievement.submittedBy',
                'reviewer',
            ])
            ->whereHas('achievement', fn (Builder $achievementQuery): Builder => $achievementQuery
                ->whereIn('submission_status', [
                    SubmissionStatus::Submitted->value,
                    SubmissionStatus::Returned->value,
                    SubmissionStatus::Validated->value,
                ]));

        $user = auth()->user();

        if ($user === null || ! $user->can('viewAny', AchievementReview::class)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->forUser($user);
    }

    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->can('viewAny', AchievementReview::class);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return static::can('update', $record);
    }

    public static function canDelete(Model $record): bool
    {
        return static::can('delete', $record);
    }

    public static function form(Schema $schema): Schema
    {
        return AchievementReviewForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AchievementReviewInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AchievementReviewsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAchievementReviews::route('/'),
            'view' => ViewAchievementReview::route('/{record}'),
        ];
    }
}
