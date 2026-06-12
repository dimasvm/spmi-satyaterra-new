<?php

namespace App\Filament\Resources\AmiAudits\RelationManagers;

use App\Enums\AmiAuditorRole;
use App\Filament\Resources\AmiAudits\AmiAuditResource;
use App\Models\AmiAuditor;
use App\Models\User;
use Closure;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AuditorAssignmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'auditorAssignments';

    protected static ?string $title = 'Auditor';

    protected static ?string $modelLabel = 'Auditor';

    protected static ?string $pluralModelLabel = 'Auditor';

    public static function canViewForRecord($ownerRecord, string $pageClass): bool
    {
        return (bool) auth()->user()?->hasAnyRole(['super_admin', 'admin_lpm', 'pimpinan', 'auditor', 'unit_pic']);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('Auditor')
                    ->relationship(
                        name: 'user',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query): Builder => $this->auditorUserQuery($query),
                    )
                    ->getOptionLabelFromRecordUsing(fn (User $record): string => trim($record->name.' - '.($record->unit?->name ?? 'Tanpa Unit')))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->rules([
                        fn (?AmiAuditor $record): Closure => function (string $attribute, mixed $value, Closure $fail) use ($record): void {
                            if (blank($value)) {
                                return;
                            }

                            $user = User::query()->find($value);

                            if ($user?->unit_id !== null && $user->unit_id === $this->getOwnerRecord()->auditee_unit_id) {
                                $fail('Auditor tidak boleh berasal dari unit auditee.');
                            }

                            $exists = AmiAuditor::query()
                                ->where('ami_audit_id', $this->getOwnerRecord()->getKey())
                                ->where('user_id', $value)
                                ->when($record !== null, fn (Builder $query): Builder => $query->whereKeyNot($record->getKey()))
                                ->exists();

                            if ($exists) {
                                $fail('User ini sudah terdaftar sebagai auditor pada audit yang sama.');
                            }
                        },
                    ]),
                Select::make('role')
                    ->label('Peran')
                    ->options(AmiAuditorRole::class)
                    ->default(AmiAuditorRole::Member->value)
                    ->required()
                    ->rules([
                        fn (?AmiAuditor $record): Closure => function (string $attribute, mixed $value, Closure $fail) use ($record): void {
                            $leadCount = $this->getOwnerRecord()
                                ->auditorAssignments()
                                ->where('role', AmiAuditorRole::Lead->value)
                                ->when($record !== null, fn (Builder $query): Builder => $query->whereKeyNot($record->getKey()))
                                ->count();

                            if ($value !== AmiAuditorRole::Lead->value && $leadCount < 1) {
                                $fail('Audit harus memiliki minimal satu lead auditor.');
                            }

                        },
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('user.unit'))
            ->recordTitleAttribute('user.name')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Auditor')
                    ->searchable(),
                TextColumn::make('user.unit.name')
                    ->label('Unit')
                    ->placeholder('Tanpa Unit')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('role')
                    ->label('Peran')
                    ->badge(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->visible(fn (): bool => AmiAuditResource::canManageAmi()),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn (): bool => AmiAuditResource::canManageAmi()),
                DeleteAction::make()
                    ->visible(fn (): bool => AmiAuditResource::canManageAmi())
                    ->before(function (DeleteAction $action, AmiAuditor $record): void {
                        if (
                            $record->role === AmiAuditorRole::Lead
                            && $this->getOwnerRecord()->auditorAssignments()->where('role', AmiAuditorRole::Lead->value)->count() <= 1
                        ) {
                            Notification::make()
                                ->danger()
                                ->title('Lead auditor terakhir tidak bisa dihapus.')
                                ->body('Audit harus memiliki minimal satu lead auditor.')
                                ->send();

                            $action->halt();
                        }
                    }),
            ]);
    }

    private function auditorUserQuery(Builder $query): Builder
    {
        return $query
            ->with('unit')
            ->where('is_active', true)
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('unit_id')
                    ->orWhere('unit_id', '!=', $this->getOwnerRecord()->auditee_unit_id);
            });
    }
}
