<?php

namespace App\Filament\Resources\IndicatorUnitAssignments\Schemas;

use App\Enums\IndicatorAssignmentStatus;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class IndicatorUnitAssignmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('status')
                    ->options(IndicatorAssignmentStatus::class),
            ]);
    }
}
