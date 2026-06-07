<?php

namespace App\Filament\Resources\IndicatorAchievements\Pages;

use App\Filament\Resources\IndicatorAchievements\IndicatorAchievementResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListIndicatorAchievements extends ListRecords
{
    protected static string $resource = IndicatorAchievementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
