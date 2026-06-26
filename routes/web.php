<?php

use App\Http\Controllers\AchievementEvidenceController;
use App\Http\Controllers\AmiAuditPdfExportController;
use App\Http\Controllers\CampusProfileController;
use App\Http\Controllers\QualityDocumentFileController;
use App\Http\Controllers\ReportExportController;
use App\Http\Controllers\SpmiDashboardExportController;
use App\Http\Controllers\SpmiEfektivitasSiklusExportController;
use Illuminate\Support\Facades\Route;

Route::get('/', CampusProfileController::class)->name('home');

Route::get('/achievement-evidences/{evidence}', AchievementEvidenceController::class)
    ->middleware(['auth', 'signed:relative'])
    ->name('achievement-evidences.show');

Route::get('/quality-documents/{document}/file', QualityDocumentFileController::class)
    ->middleware(['auth', 'signed:relative'])
    ->name('quality-documents.file');

Route::get('/reports/export', ReportExportController::class)
    ->middleware(['auth'])
    ->name('reports.export');

Route::get('/dashboard/export-pdf', SpmiDashboardExportController::class)
    ->middleware(['auth'])
    ->name('dashboard.export-pdf');

Route::get('/dashboard/export-efektivitas-pdf', SpmiEfektivitasSiklusExportController::class)
    ->middleware(['auth'])
    ->name('dashboard.export-efektivitas-pdf');

Route::get('/ami-audits/{record}/export-pdf', AmiAuditPdfExportController::class)
    ->middleware(['auth'])
    ->name('ami-audits.export-pdf');
