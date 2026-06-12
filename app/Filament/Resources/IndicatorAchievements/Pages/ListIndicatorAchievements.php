<?php

namespace App\Filament\Resources\IndicatorAchievements\Pages;

use App\Enums\SubmissionStatus;
use App\Filament\Resources\IndicatorAchievements\IndicatorAchievementResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListIndicatorAchievements extends ListRecords
{
    protected static string $resource = IndicatorAchievementResource::class;

    /**
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make('Semua')
                ->badge($this->getBaseAchievementQuery()->count()),
        ];

        foreach (SubmissionStatus::cases() as $status) {
            $tabs[$status->value] = Tab::make($status->getLabel())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('submission_status', $status->value))
                ->badge($this->getBaseAchievementQuery()->where('submission_status', $status->value)->count() ?: null)
                ->badgeColor($status->getColor());
        }

        return $tabs;
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    private function getBaseAchievementQuery(): Builder
    {
        return static::getResource()::getEloquentQuery();
    }
}
