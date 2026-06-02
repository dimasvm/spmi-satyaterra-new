<?php

namespace App\Filament\Resources\SpmiPeriods;

use App\Filament\Resources\SpmiPeriods\Pages\CreateSpmiPeriod;
use App\Filament\Resources\SpmiPeriods\Pages\EditSpmiPeriod;
use App\Filament\Resources\SpmiPeriods\Pages\ListSpmiPeriods;
use App\Filament\Resources\SpmiPeriods\Schemas\SpmiPeriodForm;
use App\Filament\Resources\SpmiPeriods\Tables\SpmiPeriodsTable;
use App\Models\SpmiPeriod;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SpmiPeriodResource extends Resource
{
    protected static ?string $model = SpmiPeriod::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Periode SPMI';

    protected static ?string $pluralModelLabel = 'Periode SPMI';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return SpmiPeriodForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SpmiPeriodsTable::configure($table);
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
            'index' => ListSpmiPeriods::route('/'),
            'create' => CreateSpmiPeriod::route('/create'),
            'edit' => EditSpmiPeriod::route('/{record}/edit'),
        ];
    }
}
