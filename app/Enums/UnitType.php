<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum UnitType: string implements HasColor, HasLabel
{
    case University = 'university';
    case Faculty = 'faculty';
    case StudyProgram = 'study_program';
    case Bureau = 'bureau';
    case Institution = 'institution';
    case Department = 'department';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::University => 'Universitas',
            self::Faculty => 'Fakultas',
            self::StudyProgram => 'Program Studi',
            self::Bureau => 'Biro',
            self::Institution => 'Lembaga',
            self::Department => 'Departemen',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::University => 'success',
            self::Faculty => 'info',
            self::StudyProgram => 'warning',
            self::Bureau, self::Institution, self::Department => 'gray',
        };
    }
}
