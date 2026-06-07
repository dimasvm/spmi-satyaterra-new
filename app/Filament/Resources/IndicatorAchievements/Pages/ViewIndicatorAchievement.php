<?php

namespace App\Filament\Resources\IndicatorAchievements\Pages;

use App\Filament\Resources\IndicatorAchievements\IndicatorAchievementResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewIndicatorAchievement extends ViewRecord
{
    protected static string $resource = IndicatorAchievementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
