<?php

namespace App\Filament\Resources\AmiChecklists\Pages;

use App\Filament\Resources\AmiChecklists\AmiChecklistResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAmiChecklist extends CreateRecord
{
    protected static string $resource = AmiChecklistResource::class;
}
