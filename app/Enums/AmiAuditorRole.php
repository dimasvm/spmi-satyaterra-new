<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum AmiAuditorRole: string implements HasColor, HasLabel
{
    case Lead = 'lead';
    case Member = 'member';
    case Observer = 'observer';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Lead => 'Ketua',
            self::Member => 'Anggota',
            self::Observer => 'Observer',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Lead => 'success',
            self::Member => 'info',
            self::Observer => 'gray',
        };
    }
}
