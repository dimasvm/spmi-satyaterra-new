<?php

namespace App\Http\Controllers;

use App\Models\QualityDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QualityDocumentFileController extends Controller
{
    public function __invoke(Request $request, QualityDocument $document): StreamedResponse
    {
        abort_unless($request->user()?->can('view', $document), 403);
        abort_if(blank($document->file_path), 404);

        $disk = Storage::disk(config('filament.default_filesystem_disk'));

        abort_unless($disk->exists($document->file_path), 404);

        $fileName = str_replace(['"', '\\'], '', basename($document->file_path));

        return $disk->response($document->file_path, $fileName, [
            'Content-Disposition' => "inline; filename=\"{$fileName}\"",
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
