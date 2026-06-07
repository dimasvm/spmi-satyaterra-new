<?php

namespace App\Filament\Resources\IndicatorAchievements\Schemas;

use App\Enums\AchievementStatus;
use App\Enums\SubmissionStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class IndicatorAchievementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('assignment_id')
                    ->relationship('assignment', 'id')
                    ->required(),
                TextInput::make('realization_value')
                    ->numeric(),
                Textarea::make('realization_text')
                    ->columnSpanFull(),
                Select::make('achievement_status')
                    ->options(AchievementStatus::class),
                Textarea::make('notes')
                    ->columnSpanFull(),
                Select::make('submission_status')
                    ->options(SubmissionStatus::class)
                    ->default('draft')
                    ->required(),
                DateTimePicker::make('submitted_at'),
                TextInput::make('submitted_by')
                    ->numeric(),
            ]);
    }
}
