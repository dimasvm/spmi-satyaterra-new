<?php

namespace App\Filament\Resources\QualityStandards\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StatementsRelationManager extends RelationManager
{
    protected static string $relationship = 'statements';

    protected static ?string $title = 'Pernyataan Standar';

    protected static ?string $modelLabel = 'Pernyataan Standar';

    protected static ?string $pluralModelLabel = 'Pernyataan Standar';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Kode')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(1),
                TextInput::make('sort_order')
                    ->label('Urutan')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->default(fn (): int => ((int) $this->getOwnerRecord()->statements()->max('sort_order')) + 1)
                    ->columnSpan(1),
                Textarea::make('statement')
                    ->label('Pernyataan Standar')
                    ->required()
                    ->rows(5)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('code')
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('statement')
                    ->label('Pernyataan Standar')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('indicators_count')
                    ->label('Jumlah Indikator')
                    ->counts('indicators')
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->numeric()
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Pernyataan'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->hidden(fn ($record): bool => $record->indicators()->exists()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
