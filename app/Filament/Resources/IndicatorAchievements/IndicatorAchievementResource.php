<?php

namespace App\Filament\Resources\IndicatorAchievements;

use App\Filament\Resources\IndicatorAchievements\Pages\CreateIndicatorAchievement;
use App\Filament\Resources\IndicatorAchievements\Pages\EditIndicatorAchievement;
use App\Filament\Resources\IndicatorAchievements\Pages\ListIndicatorAchievements;
use App\Filament\Resources\IndicatorAchievements\Pages\ViewIndicatorAchievement;
use App\Filament\Resources\IndicatorAchievements\Schemas\IndicatorAchievementForm;
use App\Filament\Resources\IndicatorAchievements\Schemas\IndicatorAchievementInfolist;
use App\Filament\Resources\IndicatorAchievements\Tables\IndicatorAchievementsTable;
use App\Models\IndicatorAchievement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class IndicatorAchievementResource extends Resource
{
    protected static ?string $model = IndicatorAchievement::class;

    protected static ?string $modelLabel = 'Capaian Indikator';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowTrendingUp;

    protected static string|UnitEnum|null $navigationGroup = 'Pelaksanaan';

    public static function form(Schema $schema): Schema
    {
        return IndicatorAchievementForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return IndicatorAchievementInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IndicatorAchievementsTable::configure($table);
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
            'index' => ListIndicatorAchievements::route('/'),
            'create' => CreateIndicatorAchievement::route('/create'),
            'view' => ViewIndicatorAchievement::route('/{record}'),
            'edit' => EditIndicatorAchievement::route('/{record}/edit'),
        ];
    }
}
