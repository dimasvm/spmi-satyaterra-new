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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class StandardIndicatorResource extends Resource
{
    protected static ?string $model = StandardIndicator::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'Penetapan';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Indikator Standar';

    protected static ?string $modelLabel = 'Indikator Standar';

    protected static ?string $pluralModelLabel = 'Indikator Standar';

    protected static ?string $recordTitleAttribute = 'code';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['assignments.unit', 'qualityStandard.category', 'standardStatement']);
    }

    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->can('viewAny', StandardIndicator::class);
    }

    public static function canCreate(): bool
    {
        return (bool) auth()->user()?->can('create', StandardIndicator::class);
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
