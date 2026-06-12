<?php

use App\Http\Controllers\AchievementEvidenceController;
use App\Http\Controllers\QualityDocumentFileController;
use App\Http\Controllers\ReportExportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('filament.admin.auth.login');
});

Route::get('/achievement-evidences/{evidence}', AchievementEvidenceController::class)
    ->middleware(['auth', 'signed:relative'])
    ->name('achievement-evidences.show');

Route::get('/quality-documents/{document}/file', QualityDocumentFileController::class)
    ->middleware(['auth', 'signed:relative'])
    ->name('quality-documents.file');

Route::get('/reports/export', ReportExportController::class)
    ->middleware(['auth'])
    ->name('reports.export');
