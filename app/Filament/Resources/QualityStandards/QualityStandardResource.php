<?php

namespace App\Filament\Resources\QualityStandards;

use App\Filament\Resources\QualityStandards\Pages\CreateQualityStandard;
use App\Filament\Resources\QualityStandards\Pages\EditQualityStandard;
use App\Filament\Resources\QualityStandards\Pages\ListQualityStandards;
use App\Filament\Resources\QualityStandards\Pages\ViewQualityStandard;
use App\Filament\Resources\QualityStandards\RelationManagers\AchievementsRelationManager;
use App\Filament\Resources\QualityStandards\RelationManagers\AssignmentsRelationManager;
use App\Filament\Resources\QualityStandards\RelationManagers\DocumentsRelationManager;
use App\Filament\Resources\QualityStandards\RelationManagers\IndicatorsRelationManager;
use App\Filament\Resources\QualityStandards\RelationManagers\StatementsRelationManager;
use App\Filament\Resources\QualityStandards\Schemas\QualityStandardForm;
use App\Filament\Resources\QualityStandards\Schemas\QualityStandardInfolist;
use App\Filament\Resources\QualityStandards\Tables\QualityStandardsTable;
use App\Filament\Resources\QualityStandards\Widgets\QualityStandardOverview;
use App\Models\QualityStandard;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class QualityStandardResource extends Resource
{
    protected static ?string $model = QualityStandard::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Penetapan';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Standar Mutu';

    protected static ?string $modelLabel = 'Standar Mutu';

    protected static ?string $pluralModelLabel = 'Standar Mutu';

    protected static ?string $recordTitleAttribute = 'name';

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        return (bool) ($user?->isSuperAdmin() || $user?->isAdminLpm());
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['category.parent', 'spmiPeriod', 'approver'])
            ->withCount(['statements', 'indicators', 'documents', 'assignments']);
    }

    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->can('viewAny', QualityStandard::class);
    }

    public static function canCreate(): bool
    {
        return (bool) auth()->user()?->can('create', QualityStandard::class);
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
        return QualityStandardForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return QualityStandardInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return QualityStandardsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            StatementsRelationManager::class,
            IndicatorsRelationManager::class,
            DocumentsRelationManager::class,
            AssignmentsRelationManager::class,
            AchievementsRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            QualityStandardOverview::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListQualityStandards::route('/'),
            'create' => CreateQualityStandard::route('/create'),
            'edit' => EditQualityStandard::route('/{record}/edit'),
            'view' => ViewQualityStandard::route('/{record}/view'),
        ];
    }
}
