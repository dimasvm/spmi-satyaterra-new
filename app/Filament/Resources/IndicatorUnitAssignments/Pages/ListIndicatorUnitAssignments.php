<?php

namespace App\Filament\Resources\IndicatorUnitAssignments\Pages;

use App\Enums\IndicatorAssignmentStatus;
use App\Filament\Resources\IndicatorUnitAssignments\IndicatorUnitAssignmentResource;
use App\Models\IndicatorUnitAssignment;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListIndicatorUnitAssignments extends ListRecords
{
    protected static string $resource = IndicatorUnitAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return $this->configureTabs();
    }

    private function configureTabs()
    {
        $tabs = [
            'Semua' => Tab::make(),
        ];

        foreach (IndicatorAssignmentStatus::cases() as $status) {
            $tabs[$status->getLabel()] = Tab::make()
                ->modifyQueryUsing(fn ($query) => $query->where('status', $status))
                ->badge(fn ($livewire) => $this->getBadgeCount($livewire, $status))
                ->badgeColor($status->getColor());
        }

        return $tabs;
    }

    private function getBadgeCount($livewire, $status)
    {
        $query = IndicatorUnitAssignment::query();
        foreach ($livewire->tableFilters as $key => $filter) {
            if (filled($filter['value'])) {
                $query->where($key, $filter['value']);
            }
        }

        $count = $query->where('status', $status)->count();

        return $count > 0 ? $count : null;
    }
}
