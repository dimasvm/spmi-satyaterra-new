<?php

namespace App\Filament\Resources\StandardCategories\Schemas;

use App\Models\StandardCategory;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StandardCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(static::components());
    }

    public static function components(): array
    {
        return [
            Section::make('Informasi Kategori')
                ->schema([
                    Select::make('parent_id')
                        ->label('Parent Kategori')
                        ->options(fn (?StandardCategory $record): array => StandardCategory::parentOptions($record?->getKey()))
                        ->searchable()
                        ->preload()
                        ->placeholder('Kategori utama')
                        ->helperText('Kosongkan untuk kategori utama. Isi untuk membuat subkategori.')
                        ->visible(fn (?StandardCategory $record): bool => ! ($record?->children()->exists() ?? false))
                        ->columnSpanFull(),
                    TextInput::make('code')
                        ->label('Kode')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->columnSpan(1),
                    TextInput::make('name')
                        ->label('Nama')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(1),
                    Textarea::make('description')
                        ->label('Deskripsi')
                        ->rows(4)
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->columnSpanFull(),
        ];
    }
}
