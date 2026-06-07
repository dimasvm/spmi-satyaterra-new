<?php

namespace App\Filament\Resources\IndicatorAchievements\Pages;

use App\Filament\Resources\IndicatorAchievements\IndicatorAchievementResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditIndicatorAchievement extends EditRecord
{
    protected static string $resource = IndicatorAchievementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
