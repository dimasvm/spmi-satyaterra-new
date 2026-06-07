<?php

namespace App\Filament\Resources\QualityStandards\Pages;

use App\Filament\Resources\QualityStandards\QualityStandardResource;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;

class ViewQualityStandard extends ViewRecord
{
    protected static string $resource = QualityStandardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make([
                TextEntry::make('name')
                    ->label('Nama Standar')
                    ->helperText(fn ($record) => $record->code),
                TextEntry::make('spmiPeriod.name')
                    ->label('Periode'),
                TextEntry::make('category.name')
                    ->label('Kategori')
                    ->badge(),
                TextEntry::make('description')
                    ->columnSpanFull()
                    ->color(Color::Gray)
                    ->label('Deskripsi'),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('version')
                    ->badge(),
                TextEntry::make('created_at')
                    ->label('Tgl Dibuat')
                    ->dateTime('d/m/Y H:i')
            ])
            ->columns(4)->columnSpanFull()
        ]);
    }
}
