<?php

namespace App\Filament\Resources\StandardIndicators;

use App\Filament\Resources\StandardIndicators\Pages\CreateStandardIndicator;
use App\Filament\Resources\StandardIndicators\Pages\EditStandardIndicator;
use App\Filament\Resources\StandardIndicators\Pages\ListStandardIndicators;
use App\Filament\Resources\StandardIndicators\Schemas\StandardIndicatorForm;
use App\Filament\Resources\StandardIndicators\Tables\StandardIndicatorsTable;
use App\Models\StandardIndicator;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class StandardIndicatorResource extends Resource
{
    protected static ?string $model = StandardIndicator::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'Penetapan';

    protected static ?int $navigationSort = 6;

    protected static ?string $modelLabel = 'Indikator Standar';

    protected static ?string $pluralModelLabel = 'Indikator Standar';

    protected static ?string $recordTitleAttribute = 'code';

    public static function form(Schema $schema): Schema
    {
        return StandardIndicatorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StandardIndicatorsTable::configure($table);
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
            'index' => ListStandardIndicators::route('/'),
            'create' => CreateStandardIndicator::route('/create'),
            'edit' => EditStandardIndicator::route('/{record}/edit'),
        ];
    }
}
