<?php

namespace App\Filament\Resources\CorrectiveActions\Pages;

use App\Enums\CorrectiveActionStatus;
use App\Filament\Resources\CorrectiveActions\CorrectiveActionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCorrectiveAction extends CreateRecord
{
    protected static string $resource = CorrectiveActionResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = CorrectiveActionStatus::Draft;
        $data['submitted_at'] = null;
        $data['submitted_by'] = null;

        return $data;
    }
}
