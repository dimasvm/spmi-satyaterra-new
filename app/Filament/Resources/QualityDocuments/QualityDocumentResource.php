<?php

namespace App\Filament\Resources\QualityDocuments;

use App\Filament\Resources\QualityDocuments\Pages\CreateQualityDocument;
use App\Filament\Resources\QualityDocuments\Pages\EditQualityDocument;
use App\Filament\Resources\QualityDocuments\Pages\ListQualityDocuments;
use App\Filament\Resources\QualityDocuments\Pages\ViewQualityDocument;
use App\Filament\Resources\QualityDocuments\Schemas\QualityDocumentForm;
use App\Filament\Resources\QualityDocuments\Schemas\QualityDocumentInfolist;
use App\Filament\Resources\QualityDocuments\Tables\QualityDocumentsTable;
use App\Models\QualityDocument;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class QualityDocumentResource extends Resource
{
    protected static ?string $model = QualityDocument::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolderOpen;

    protected static string|UnitEnum|null $navigationGroup = 'Penetapan';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Dokumen Mutu';

    protected static ?string $modelLabel = 'Dokumen Mutu';

    protected static ?string $pluralModelLabel = 'Dokumen Mutu';

    protected static ?string $recordTitleAttribute = 'title';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['qualityStandard', 'spmiPeriod', 'uploadedBy', 'approvedBy']);

        $user = auth()->user();

        if ($user === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->visibleToUser($user);
    }

    public static function canCreate(): bool
    {
        return (bool) auth()->user()?->can('create', QualityDocument::class);
    }

    public static function canEdit(Model $record): bool
    {
        return static::can('update', $record);
    }

    public static function canDelete(Model $record): bool
    {
        return static::can('delete', $record);
    }

    public static function canDeleteAny(): bool
    {
        return (bool) auth()->user()?->can('deleteAny', QualityDocument::class);
    }

    public static function form(Schema $schema): Schema
    {
        return QualityDocumentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return QualityDocumentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return QualityDocumentsTable::configure($table);
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
            'index' => ListQualityDocuments::route('/'),
            'create' => CreateQualityDocument::route('/create'),
            'view' => ViewQualityDocument::route('/{record}'),
            'edit' => EditQualityDocument::route('/{record}/edit'),
        ];
    }
}
