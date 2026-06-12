<?php

namespace App\Filament\Resources\CorrectiveActions;

use App\Filament\Resources\CorrectiveActions\Pages\CreateCorrectiveAction;
use App\Filament\Resources\CorrectiveActions\Pages\EditCorrectiveAction;
use App\Filament\Resources\CorrectiveActions\Pages\ListCorrectiveActions;
use App\Filament\Resources\CorrectiveActions\Pages\ViewCorrectiveAction;
use App\Filament\Resources\CorrectiveActions\RelationManagers\EvidencesRelationManager;
use App\Filament\Resources\CorrectiveActions\Schemas\CorrectiveActionForm;
use App\Filament\Resources\CorrectiveActions\Schemas\CorrectiveActionInfolist;
use App\Filament\Resources\CorrectiveActions\Tables\CorrectiveActionsTable;
use App\Models\CorrectiveAction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class CorrectiveActionResource extends Resource
{
    protected static ?string $model = CorrectiveAction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static string|UnitEnum|null $navigationGroup = 'AMI';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Tindak Lanjut';

    protected static ?string $modelLabel = 'Tindak Lanjut Temuan';

    protected static ?string $pluralModelLabel = 'Tindak Lanjut Temuan';

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        return (bool) ($user?->isSuperAdmin() || $user?->isAdminLpm() || $user?->isAuditor())
            && (bool) $user?->can('corrective-actions.view');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with([
                'finding.audit.auditeeUnit',
                'finding.audit.auditorAssignments',
                'finding.standardIndicator',
                'picUser',
                'submittedBy',
                'latestReview.reviewer',
                'evidences',
            ]);

        $user = auth()->user();

        if ($user === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->visibleToUser($user);
    }

    public static function canCreate(): bool
    {
        return (bool) auth()->user()?->can('create', CorrectiveAction::class);
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
        return (bool) auth()->user()?->can('deleteAny', CorrectiveAction::class);
    }

    public static function form(Schema $schema): Schema
    {
        return CorrectiveActionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CorrectiveActionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CorrectiveActionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            EvidencesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCorrectiveActions::route('/'),
            'create' => CreateCorrectiveAction::route('/create'),
            'view' => ViewCorrectiveAction::route('/{record}'),
            'edit' => EditCorrectiveAction::route('/{record}/edit'),
        ];
    }
}
