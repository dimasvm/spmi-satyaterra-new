<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;

class Login extends BaseLogin
{
    protected string $view = 'filament.pages.auth.login';

    protected Width|string|null $maxWidth = Width::Screen;

    public function getTitle(): string|Htmlable
    {
        return 'Masuk SPMI';
    }

    public function getHeading(): string|Htmlable|null
    {
        return null;
    }

    public function getSubheading(): string|Htmlable|null
    {
        return null;
    }

    public function hasLogo(): bool
    {
        return false;
    }
}
