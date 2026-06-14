<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\File;

class PanduanPenggunaan extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'panduan-penggunaan';

    protected static ?string $title = 'Panduan Penggunaan Aplikasi';

    protected string $view = 'filament.pages.panduan-penggunaan';

    public function guideHtml(): string
    {
        $path = base_path('docs/panduan-penggunaan-aplikasi.md');

        abort_unless(File::exists($path), 404);

        return str(File::get($path))
            ->markdown()
            ->sanitizeHtml()
            ->toString();
    }
}
