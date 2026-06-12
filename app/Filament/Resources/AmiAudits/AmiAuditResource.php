<?php

namespace App\Filament\Resources\AmiAudits;

use App\Filament\Resources\AmiAudits\Pages\CreateAmiAudit;
use App\Filament\Resources\AmiAudits\Pages\EditAmiAudit;
use App\Filament\Resources\AmiAudits\Pages\ListAmiAudits;
use App\Filament\Resources\AmiAudits\Pages\ViewAmiAudit;
use App\Filament\Resources\AmiAudits\RelationManagers\AuditorAssignmentsRelationManager;
use App\Filament\Resources\AmiAudits\RelationManagers\ChecklistsRelationManager;
use App\Filament\Resources\AmiAudits\RelationManagers\FindingsRelationManager;
use App\Filament\Resources\AmiAudits\Schemas\AmiAuditForm;
use App\Filament\Resources\AmiAudits\Schemas\AmiAuditInfolist;
use App\Filament\Resources\AmiAudits\Tables\AmiAuditsTable;
use App\Models\AmiAudit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class AmiAuditResource extends Resource
{
    protected static ?string $model = AmiAudit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static string|UnitEnum|null $navigationGroup = 'AMI';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Jadwal Audit';

    protected static ?string $modelLabel = 'Audit AMI';

    protected static ?string $pluralModelLabel = 'Audit AMI';

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        return (bool) ($user?->isSuperAdmin() || $user?->isAdminLpm() || $user?->isAuditor());
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with([
                'amiPeriod.spmiPeriod',
                'auditeeUnit',
                'finalizedBy',
            ])
            ->withCount(['auditorAssignments', 'checklists']);

        $user = auth()->user();

        if ($user === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->forUser($user);
    }

    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->can('viewAny', AmiAudit::class);
    }

    public static function canCreate(): bool
    {
        return (bool) auth()->user()?->can('create', AmiAudit::class);
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
        return (bool) auth()->user()?->can('deleteAny', AmiAudit::class);
    }

    public static function canManageAmi(): bool
    {
        return (bool) auth()->user()?->can('create', AmiAudit::class);
    }

    public static function form(Schema $schema): Schema
    {
        return AmiAuditForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AmiAuditInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AmiAuditsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            AuditorAssignmentsRelationManager::class,
            ChecklistsRelationManager::class,
            FindingsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAmiAudits::route('/'),
            'create' => CreateAmiAudit::route('/create'),
            'view' => ViewAmiAudit::route('/{record}'),
            'edit' => EditAmiAudit::route('/{record}/edit'),
        ];
    }
}
