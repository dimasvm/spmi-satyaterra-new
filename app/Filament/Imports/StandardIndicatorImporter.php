<?php

namespace App\Filament\Imports;

use App\Models\StandardIndicator;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class StandardIndicatorImporter extends Importer
{
    protected static ?string $model = StandardIndicator::class;

    public static function getColumns(): array
    {
        return [
            //
        ];
    }

    public function resolveRecord(): StandardIndicator
    {
        return StandardIndicator::firstOrNew([
            'code' => $this->data['code'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your standard indicator import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
