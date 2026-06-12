<?php

namespace App\Filament\Resources\AmiChecklists;

use App\Enums\AmiAssessmentResult;
use App\Enums\AmiFindingStatus;
use App\Filament\Resources\AmiChecklists\Pages\CreateAmiChecklist;
use App\Filament\Resources\AmiChecklists\Pages\EditAmiChecklist;
use App\Filament\Resources\AmiChecklists\Pages\ListAmiChecklists;
use App\Filament\Resources\AmiChecklists\Pages\ViewAmiChecklist;
use App\Filament\Resources\AmiChecklists\Schemas\AmiChecklistForm;
use App\Filament\Resources\AmiChecklists\Schemas\AmiChecklistInfolist;
use App\Filament\Resources\AmiChecklists\Tables\AmiChecklistsTable;
use App\Filament\Resources\AmiFindings\AmiFindingResource;
use App\Filament\Resources\AmiFindings\Schemas\AmiFindingForm;
use App\Models\AmiChecklist;
use App\Models\AmiFinding;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class AmiChecklistResource extends Resource
{
    protected static ?string $model = AmiChecklist::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'AMI';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Checklist Audit';

    protected static ?string $modelLabel = 'Checklist AMI';

    protected static ?string $pluralModelLabel = 'Checklist AMI';

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        return (bool) ($user?->isSuperAdmin() || $user?->isAdminLpm() || $user?->isAuditor());
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with([
                'audit.amiPeriod.spmiPeriod',
                'audit.auditeeUnit',
                'audit.auditorAssignments',
                'standardIndicator.qualityStandard',
            ]);

        $user = auth()->user();

        if ($user === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->forUser($user);
    }

    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->can('viewAny', AmiChecklist::class);
    }

    public static function canCreate(): bool
    {
        return (bool) auth()->user()?->can('create', AmiChecklist::class);
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
        return (bool) auth()->user()?->can('deleteAny', AmiChecklist::class);
    }

    public static function canManageChecklistSetup(): bool
    {
        return (bool) auth()->user()?->can('create', AmiChecklist::class);
    }

    public static function createFindingAction(): Action
    {
        return Action::make('createFinding')
            ->label('Buat Temuan')
            ->icon(Heroicon::OutlinedExclamationTriangle)
            ->color('warning')
            ->modalHeading('Buat Temuan Audit')
            ->schema(fn (AmiChecklist $record): array => AmiFindingForm::components(
                auditId: $record->ami_audit_id,
                checklistId: $record->id,
                includeAuditField: false,
                includeChecklistField: false,
                includeIndicatorField: false,
            ))
            ->fillForm(fn (AmiChecklist $record): array => [
                'category' => $record->assessment_result?->value,
                'description' => $record->auditor_notes,
                'status' => AmiFindingStatus::Open->value,
            ])
            ->visible(fn (AmiChecklist $record): bool => static::hasFindingAssessment($record)
                && AmiFindingResource::canCreateForChecklist($record))
            ->action(function (array $data, AmiChecklist $record): void {
                AmiFinding::query()->create([
                    ...$data,
                    'ami_audit_id' => $record->ami_audit_id,
                    'ami_checklist_id' => $record->id,
                    'standard_indicator_id' => $record->standard_indicator_id,
                    'created_by' => auth()->id(),
                ]);

                Notification::make()
                    ->success()
                    ->title('Temuan audit berhasil dibuat.')
                    ->send();
            });
    }

    private static function hasFindingAssessment(AmiChecklist $record): bool
    {
        return in_array($record->assessment_result, [
            AmiAssessmentResult::Observation,
            AmiAssessmentResult::Minor,
            AmiAssessmentResult::Major,
            AmiAssessmentResult::Ofi,
        ], true);
    }

    public static function form(Schema $schema): Schema
    {
        return AmiChecklistForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AmiChecklistInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AmiChecklistsTable::configure($table);
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
            'index' => ListAmiChecklists::route('/'),
            'create' => CreateAmiChecklist::route('/create'),
            'view' => ViewAmiChecklist::route('/{record}'),
            'edit' => EditAmiChecklist::route('/{record}/edit'),
        ];
    }
}
