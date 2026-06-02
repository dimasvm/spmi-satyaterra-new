<?php

namespace App\Filament\Resources\StandardCategories\Tables;

use App\Filament\Resources\StandardCategories\Schemas\StandardCategoryForm;
use App\Models\StandardCategory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StandardCategoriesTable
{
    public static function configure(Table $table, bool $isModalTable = false): Table
    {
        $table = $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(60)
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->defaultSort('code');

        if ($isModalTable) {
            $table
                ->headerActions([
                    CreateAction::make()
                        ->label('Tambah Kategori')
                        ->modalHeading('Tambah Kategori Standar')
                        ->schema(StandardCategoryForm::components()),
                ])
                ->recordActions([
                    EditAction::make()
                        ->modalHeading('Ubah Kategori Standar')
                        ->schema(StandardCategoryForm::components()),
                    DeleteAction::make()
                        ->hidden(fn (StandardCategory $record): bool => $record->qualityStandards()->exists()),
                ]);
        } else {
            $table->recordActions([
                EditAction::make(),
            ]);
        }

        return $table
            ->checkIfRecordIsSelectableUsing(
                fn (StandardCategory $record): bool => ! $record->qualityStandards()->exists(),
            )
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
