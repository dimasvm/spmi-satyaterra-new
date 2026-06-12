<?php

namespace App\Filament\Resources\AchievementReviews\Pages;

use App\Enums\AchievementReviewStatus;
use App\Filament\Resources\AchievementReviews\AchievementReviewResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListAchievementReviews extends ListRecords
{
    protected static string $resource = AchievementReviewResource::class;

    /**
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        return $this->configureTabs();
    }

    private function configureTabs()
    {
        $tabs = [
            'Semua' => Tab::make(),
        ];

        foreach (AchievementReviewStatus::cases() as $status) {
            $tabs[$status->getLabel()] = Tab::make()
                ->modifyQueryUsing(fn ($query) => $query->where('status', $status))
                ->badge(fn ($livewire) => $this->getBadgeCount($livewire, $status))
                ->badgeColor($status->getColor());
        }

        return $tabs;
    }

    private function getBadgeCount($livewire, $status)
    {
        $query = $this->getBaseReviewQuery();

        foreach ($livewire->tableFilters as $key => $filter) {
            if (filled($filter['value'])) {
                $this->applyTableFilterToBadgeQuery($query, $key, $filter['value']);
            }
        }

        $count = $query->where('status', $status)->count();

        return $count > 0 ? $count : null;
    }

    // public function getDefaultActiveTab(): string|int|null
    // {
    //     return AchievementReviewStatus::Pending->value;
    // }

    protected function getHeaderActions(): array
    {
        return [];
    }

    private function getBaseReviewQuery(): Builder
    {
        return static::getResource()::getEloquentQuery();
    }

    private function applyTableFilterToBadgeQuery(Builder $query, string $key, mixed $value): void
    {
        match ($key) {
            'spmi_period_id' => $query->whereHas('achievement.assignment', fn (Builder $assignmentQuery): Builder => $assignmentQuery
                ->where('spmi_period_id', $value)),
            'unit_id' => $query->whereHas('achievement.assignment', fn (Builder $assignmentQuery): Builder => $assignmentQuery
                ->where('unit_id', $value)),
            'quality_standard_id' => $query->whereHas('achievement.assignment.standardIndicator', fn (Builder $indicatorQuery): Builder => $indicatorQuery
                ->where('quality_standard_id', $value)),
            'submission_status' => $query->whereHas('achievement', fn (Builder $achievementQuery): Builder => $achievementQuery
                ->where('submission_status', $value)),
            'achievement_status' => $query->whereHas('achievement', fn (Builder $achievementQuery): Builder => $achievementQuery
                ->where('achievement_status', $value)),
            default => $query->where($key, $value),
        };
    }
}
