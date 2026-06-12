<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum ReportType: string implements HasLabel
{
    case IndicatorByPeriod = 'indicator_by_period';
    case IndicatorByUnit = 'indicator_by_unit';
    case LpmValidation = 'lpm_validation';
    case AmiByPeriod = 'ami_by_period';
    case AuditFindings = 'audit_findings';
    case CorrectiveActions = 'corrective_actions';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::IndicatorByPeriod => 'Laporan Capaian Indikator per Periode',
            self::IndicatorByUnit => 'Laporan Capaian Indikator per Unit',
            self::LpmValidation => 'Laporan Validasi LPM',
            self::AmiByPeriod => 'Laporan AMI per Periode',
            self::AuditFindings => 'Laporan Temuan Audit',
            self::CorrectiveActions => 'Laporan Tindak Lanjut Temuan',
        };
    }

    public function fileNamePrefix(): string
    {
        return match ($this) {
            self::IndicatorByPeriod => 'laporan-capaian-per-periode',
            self::IndicatorByUnit => 'laporan-capaian-per-unit',
            self::LpmValidation => 'laporan-validasi-lpm',
            self::AmiByPeriod => 'laporan-ami-per-periode',
            self::AuditFindings => 'laporan-temuan-audit',
            self::CorrectiveActions => 'laporan-tindak-lanjut-temuan',
        };
    }

    public function pdfView(): string
    {
        return match ($this) {
            self::IndicatorByPeriod, self::IndicatorByUnit => 'reports.pdf.indicator-achievements',
            self::LpmValidation => 'reports.pdf.lpm-validation',
            self::AmiByPeriod => 'reports.pdf.ami-audits',
            self::AuditFindings => 'reports.pdf.audit-findings',
            self::CorrectiveActions => 'reports.pdf.corrective-actions',
        };
    }
}
