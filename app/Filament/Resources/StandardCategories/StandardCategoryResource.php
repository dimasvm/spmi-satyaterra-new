<?php

namespace App\Filament\Resources\StandardCategories;

use App\Filament\Resources\StandardCategories\Pages\CreateStandardCategory;
use App\Filament\Resources\StandardCategories\Pages\EditStandardCategory;
use App\Filament\Resources\StandardCategories\Pages\ListStandardCategories;
use App\Filament\Resources\StandardCategories\Schemas\StandardCategoryForm;
use App\Filament\Resources\StandardCategories\Tables\StandardCategoriesTable;
use App\Models\StandardCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class StandardCategoryResource extends Resource
{
    protected static ?string $model = StandardCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Kategori Standar';

    protected static ?string $modelLabel = 'Kategori Standar';

    protected static ?string $pluralModelLabel = 'Kategori Standar';

    protected static ?string $recordTitleAttribute = 'name';

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        return (bool) ($user?->isSuperAdmin() || $user?->isAdminLpm());
    }

    public static function form(Schema $schema): Schema
    {
        return StandardCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StandardCategoriesTable::configure($table);
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
            'index' => ListStandardCategories::route('/'),
            'create' => CreateStandardCategory::route('/create'),
            'edit' => EditStandardCategory::route('/{record}/edit'),
        ];
    }
}
