<?php

namespace App\Filament\Resources\AmiFindings\Pages;

use App\Filament\Resources\AmiFindings\AmiFindingResource;
use App\Models\AmiAudit;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Gate;

class CreateAmiFinding extends CreateRecord
{
    protected static string $resource = AmiFindingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $audit = AmiAudit::query()->findOrFail($data['ami_audit_id']);

        Gate::authorize('create', static::getModel());

        abort_unless(AmiFindingResource::canCreateForAudit($audit), 403);

        $data['created_by'] = auth()->id();

        return $data;
    }
}
