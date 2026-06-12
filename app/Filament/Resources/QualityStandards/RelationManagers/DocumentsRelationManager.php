<?php

namespace App\Filament\Resources\QualityStandards\RelationManagers;

use App\Filament\Resources\QualityDocuments\Schemas\QualityDocumentForm;
use App\Filament\Resources\QualityDocuments\Tables\QualityDocumentsTable;
use App\Models\QualityDocument;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $title = 'Dokumen Terkait';

    protected static ?string $modelLabel = 'Dokumen Mutu';

    protected static ?string $pluralModelLabel = 'Dokumen Mutu';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components(QualityDocumentForm::components(
                includeQualityStandard: false,
                includeSpmiPeriod: false,
            ));
    }

    public function table(Table $table): Table
    {
        return QualityDocumentsTable::configure($table)
            ->recordTitleAttribute('title')
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Dokumen')
                    ->mutateDataUsing(fn (array $data): array => [
                        ...$data,
                        'quality_standard_id' => $this->getOwnerRecord()->getKey(),
                        'spmi_period_id' => $this->getOwnerRecord()->spmi_period_id,
                        'uploaded_by' => auth()->id(),
                    ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => auth()->user()?->can('deleteAny', QualityDocument::class) ?? false),
                ]),
            ]);
    }
}
