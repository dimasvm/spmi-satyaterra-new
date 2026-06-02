<?php

namespace App\Filament\Resources\StandardCategories\Pages;

use App\Filament\Resources\StandardCategories\StandardCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStandardCategories extends ListRecords
{
    protected static string $resource = StandardCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
