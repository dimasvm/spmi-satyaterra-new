<?php

namespace App\Filament\Resources\StandardCategories\Pages;

use App\Filament\Resources\StandardCategories\StandardCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStandardCategory extends EditRecord
{
    protected static string $resource = StandardCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
