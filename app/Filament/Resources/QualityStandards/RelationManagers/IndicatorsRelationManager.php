<?php

namespace App\Filament\Resources\QualityStandards\RelationManagers;

use App\Filament\Resources\StandardIndicators\Schemas\StandardIndicatorForm;
use App\Filament\Resources\StandardIndicators\Tables\StandardIndicatorsTable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class IndicatorsRelationManager extends RelationManager
{
    protected static string $relationship = 'indicators';

    protected static ?string $title = 'Indikator';

    protected static ?string $modelLabel = 'Indikator Standar';

    protected static ?string $pluralModelLabel = 'Indikator Standar';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components(StandardIndicatorForm::components(
                includeQualityStandard: false,
                qualityStandardId: (int) $this->getOwnerRecord()->getKey(),
            ));
    }

    public function table(Table $table): Table
    {
        return StandardIndicatorsTable::configure($table, isRelationManager: true)
            ->recordTitleAttribute('code');
    }
}
