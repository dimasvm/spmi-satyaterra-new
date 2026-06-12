<?php

namespace App\Filament\Resources\AmiAudits\RelationManagers;

use App\Filament\Resources\AmiFindings\AmiFindingResource;
use App\Filament\Resources\AmiFindings\Schemas\AmiFindingForm;
use App\Filament\Resources\AmiFindings\Tables\AmiFindingsTable;
use App\Models\AmiFinding;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FindingsRelationManager extends RelationManager
{
    protected static string $relationship = 'findings';

    protected static ?string $title = 'Temuan Audit';

    protected static ?string $modelLabel = 'Temuan AMI';

    protected static ?string $pluralModelLabel = 'Temuan AMI';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components(AmiFindingForm::components(
                auditId: $this->getOwnerRecord()->id,
                includeAuditField: false,
            ));
    }

    public function table(Table $table): Table
    {
        return AmiFindingsTable::configure($table)
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['standardIndicator', 'createdBy', 'audit.auditeeUnit']))
            ->recordTitleAttribute('finding_number')
            ->headerActions([
                CreateAction::make()
                    ->visible(fn (): bool => AmiFindingResource::canCreateForAudit($this->getOwnerRecord()))
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['ami_audit_id'] = $this->getOwnerRecord()->id;
                        $data['created_by'] = auth()->id();

                        return $data;
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn (AmiFinding $record): bool => AmiFindingResource::canEdit($record)),
                DeleteAction::make()
                    ->visible(fn (AmiFinding $record): bool => AmiFindingResource::canDelete($record)),
            ]);
    }
}
