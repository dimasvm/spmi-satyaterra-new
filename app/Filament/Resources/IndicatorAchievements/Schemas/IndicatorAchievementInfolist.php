<?php

namespace App\Filament\Resources\IndicatorAchievements\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class IndicatorAchievementInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('assignment.id')
                    ->label('Assignment'),
                TextEntry::make('realization_value')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('realization_text')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('achievement_status')
                    ->badge()
                    ->placeholder('-'),
                TextEntry::make('notes')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('submission_status')
                    ->badge(),
                TextEntry::make('submitted_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('submitted_by')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
