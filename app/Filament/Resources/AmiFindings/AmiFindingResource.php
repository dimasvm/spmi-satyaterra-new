<?php

namespace App\Filament\Resources\AmiFindings;

use App\Enums\AmiAuditStatus;
use App\Filament\Resources\AmiFindings\Pages\CreateAmiFinding;
use App\Filament\Resources\AmiFindings\Pages\EditAmiFinding;
use App\Filament\Resources\AmiFindings\Pages\ListAmiFindings;
use App\Filament\Resources\AmiFindings\Pages\ViewAmiFinding;
use App\Filament\Resources\AmiFindings\Schemas\AmiFindingForm;
use App\Filament\Resources\AmiFindings\Schemas\AmiFindingInfolist;
use App\Filament\Resources\AmiFindings\Tables\AmiFindingsTable;
use App\Models\AmiAudit;
use App\Models\AmiChecklist;
use App\Models\AmiFinding;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class AmiFindingResource extends Resource
{
    protected static ?string $model = AmiFinding::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static string|UnitEnum|null $navigationGroup = 'AMI';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Temuan Audit';

    protected static ?string $modelLabel = 'Temuan AMI';

    protected static ?string $pluralModelLabel = 'Temuan AMI';

    protected static ?string $recordTitleAttribute = 'finding_number';

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        return (bool) ($user?->isSuperAdmin() || $user?->isAdminLpm() || $user?->isAuditor() || $user?->isUnitPic());
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with([
                'audit.amiPeriod',
                'audit.auditeeUnit',
                'audit.auditorAssignments',
                'standardIndicator.qualityStandard',
                'checklist',
                'createdBy',
            ]);

        $user = auth()->user();

        if ($user === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->visibleToUser($user);
    }

    public static function canCreate(): bool
    {
        return (bool) auth()->user()?->can('create', AmiFinding::class);
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
        return (bool) auth()->user()?->can('deleteAny', AmiFinding::class);
    }

    public static function canCreateForChecklist(AmiChecklist $checklist): bool
    {
        return $checklist->audit !== null && static::canCreateForAudit($checklist->audit);
    }

    public static function canCreateForAudit(AmiAudit $audit): bool
    {
        $user = auth()->user();

        if ($user === null) {
            return false;
        }

        if ($user->isAdminLpm()) {
            return true;
        }

        return $audit->status !== AmiAuditStatus::Finalized
            && $user->isAuditor()
            && $audit->auditorAssignments()->where('user_id', $user->id)->exists();
    }

    public static function form(Schema $schema): Schema
    {
        return AmiFindingForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AmiFindingInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AmiFindingsTable::configure($table);
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
            'index' => ListAmiFindings::route('/'),
            'create' => CreateAmiFinding::route('/create'),
            'view' => ViewAmiFinding::route('/{record}'),
            'edit' => EditAmiFinding::route('/{record}/edit'),
        ];
    }
}
