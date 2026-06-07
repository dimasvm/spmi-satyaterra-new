<?php

namespace App\Filament\Resources\IndicatorUnitAssignments;

use App\Filament\Resources\IndicatorUnitAssignments\Pages\CreateIndicatorUnitAssignment;
use App\Filament\Resources\IndicatorUnitAssignments\Pages\EditIndicatorUnitAssignment;
use App\Filament\Resources\IndicatorUnitAssignments\Pages\ListIndicatorUnitAssignments;
use App\Filament\Resources\IndicatorUnitAssignments\Pages\ViewIndicatorUnitAssignment;
use App\Filament\Resources\IndicatorUnitAssignments\Schemas\IndicatorUnitAssignmentForm;
use App\Filament\Resources\IndicatorUnitAssignments\Schemas\IndicatorUnitAssignmentInfolist;
use App\Filament\Resources\IndicatorUnitAssignments\Tables\IndicatorUnitAssignmentsTable;
use App\Models\IndicatorUnitAssignment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class IndicatorUnitAssignmentResource extends Resource
{
    protected static ?string $model = IndicatorUnitAssignment::class;

    protected static ?string $modelLabel = 'Penugasan Unit';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserPlus;
    protected static string|UnitEnum|null $navigationGroup = 'Penetapan';

    public static function form(Schema $schema): Schema
    {
        return IndicatorUnitAssignmentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return IndicatorUnitAssignmentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IndicatorUnitAssignmentsTable::configure($table);
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
            'index' => ListIndicatorUnitAssignments::route('/'),
            'create' => CreateIndicatorUnitAssignment::route('/create'),
            'view' => ViewIndicatorUnitAssignment::route('/{record}'),
            'edit' => EditIndicatorUnitAssignment::route('/{record}/edit'),
        ];
    }
}
