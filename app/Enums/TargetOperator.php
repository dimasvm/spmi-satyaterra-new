<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum TargetOperator: string implements HasColor, HasLabel
{
    case GreaterThanOrEqual = '>=';
    case LessThanOrEqual = '<=';
    case Equal = '=';
    case GreaterThan = '>';
    case LessThan = '<';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::GreaterThanOrEqual => '>=',
            self::LessThanOrEqual => '<=',
            self::Equal => '=',
            self::GreaterThan => '>',
            self::LessThan => '<',
        };
    }

    public function getColor(): string|array|null
    {
        return 'gray';
    }
}
