<?php

namespace App\Filament\Resources\AmiPeriods;

use App\Filament\Resources\AmiPeriods\Pages\CreateAmiPeriod;
use App\Filament\Resources\AmiPeriods\Pages\EditAmiPeriod;
use App\Filament\Resources\AmiPeriods\Pages\ListAmiPeriods;
use App\Filament\Resources\AmiPeriods\Pages\ViewAmiPeriod;
use App\Filament\Resources\AmiPeriods\Schemas\AmiPeriodForm;
use App\Filament\Resources\AmiPeriods\Schemas\AmiPeriodInfolist;
use App\Filament\Resources\AmiPeriods\Tables\AmiPeriodsTable;
use App\Models\AmiPeriod;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class AmiPeriodResource extends Resource
{
    protected static ?string $model = AmiPeriod::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static string|UnitEnum|null $navigationGroup = 'AMI';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Periode AMI';

    protected static ?string $modelLabel = 'Periode AMI';

    protected static ?string $pluralModelLabel = 'Periode AMI';

    protected static ?string $recordTitleAttribute = 'name';

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        return (bool) ($user?->isSuperAdmin() || $user?->isAdminLpm() || $user?->isAuditor());
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['spmiPeriod'])
            ->withCount('audits');

        $user = auth()->user();

        if ($user === null) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isSuperAdmin() || $user->isAdminLpm() || $user->isPimpinan()) {
            return $query;
        }

        if ($user->isAuditor()) {
            return $query->whereHas('audits.auditorAssignments', fn (Builder $auditQuery): Builder => $auditQuery
                ->where('user_id', $user->id));
        }

        if ($user->isUnitPic() && $user->unit_id !== null) {
            return $query->whereHas('audits', fn (Builder $auditQuery): Builder => $auditQuery
                ->where('auditee_unit_id', $user->unit_id));
        }

        return $query->whereRaw('1 = 0');
    }

    public static function canCreate(): bool
    {
        return (bool) auth()->user()?->can('create', AmiPeriod::class);
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
        return (bool) auth()->user()?->can('deleteAny', AmiPeriod::class);
    }

    public static function canManageAmi(): bool
    {
        return (bool) auth()->user()?->can('create', AmiPeriod::class);
    }

    public static function form(Schema $schema): Schema
    {
        return AmiPeriodForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AmiPeriodInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AmiPeriodsTable::configure($table);
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
            'index' => ListAmiPeriods::route('/'),
            'create' => CreateAmiPeriod::route('/create'),
            'view' => ViewAmiPeriod::route('/{record}'),
            'edit' => EditAmiPeriod::route('/{record}/edit'),
        ];
    }
}
