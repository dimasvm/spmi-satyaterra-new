<?php

namespace App\Filament\Resources\IndicatorUnitAssignments;

use App\Filament\Resources\IndicatorUnitAssignments\Pages\CreateIndicatorUnitAssignment;
use App\Filament\Resources\IndicatorUnitAssignments\Pages\EditIndicatorUnitAssignment;
use App\Filament\Resources\IndicatorUnitAssignments\Pages\ListIndicatorUnitAssignments;
use App\Filament\Resources\IndicatorUnitAssignments\Schemas\IndicatorUnitAssignmentForm;
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

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static string|UnitEnum|null $navigationGroup = 'SPMI';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Penugasan Indikator';

    protected static ?string $pluralModelLabel = 'Penugasan Indikator';

    public static function form(Schema $schema): Schema
    {
        return IndicatorUnitAssignmentForm::configure($schema);
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
            'edit' => EditIndicatorUnitAssignment::route('/{record}/edit'),
        ];
    }
}
