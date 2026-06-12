<?php

namespace App\Http\Controllers;

use App\Enums\ReportType;
use App\Exports\Reports\AmiAuditReportExport;
use App\Exports\Reports\AuditFindingReportExport;
use App\Exports\Reports\CorrectiveActionReportExport;
use App\Exports\Reports\IndicatorAchievementByPeriodExport;
use App\Exports\Reports\IndicatorAchievementByUnitExport;
use App\Exports\Reports\LpmValidationExport;
use App\Exports\Reports\ReportExport;
use App\Services\Reports\ReportQueryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class ReportExportController extends Controller
{
    public function __invoke(Request $request, ReportQueryService $reports): Response|BinaryFileResponse
    {
        abort_unless($request->user()?->can('reports.export'), 403);

        $type = ReportType::tryFrom((string) $request->query('jenis_laporan')) ?? ReportType::IndicatorByPeriod;
        $format = (string) $request->query('format', 'pdf');
        $filters = $request->only([
            'jenis_laporan',
            'spmi_period_id',
            'ami_period_id',
            'unit_id',
            'standard_category_id',
            'status',
            'date_from',
            'date_until',
        ]);

        return match ($format) {
            'excel' => $this->downloadExcel($type, $filters, $reports),
            default => $this->downloadPdf($type, $filters, $reports),
        };
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function downloadPdf(ReportType $type, array $filters, ReportQueryService $reports): Response
    {
        abort_unless(class_exists(Pdf::class), 404, 'Package PDF belum tersedia.');

        return Pdf::loadView($type->pdfView(), [
            'title' => $type->getLabel(),
            'headings' => $reports->headings($type),
            'rows' => $reports->rows($type, $filters),
            'generatedAt' => now(),
        ])->download($this->fileName($type, 'pdf'));
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function downloadExcel(ReportType $type, array $filters, ReportQueryService $reports): BinaryFileResponse
    {
        abort_unless(class_exists(Excel::class), 404, 'Package Excel belum tersedia.');

        $export = $this->exportForType($type, $filters, $reports);
        $adapter = new class($export) implements FromArray, WithHeadings
        {
            public function __construct(private ReportExport $export) {}

            /**
             * @return array<int, string>
             */
            public function headings(): array
            {
                return $this->export->headings();
            }

            /**
             * @return array<int, array<int, mixed>>
             */
            public function array(): array
            {
                return $this->export->rows()->all();
            }
        };

        return Excel::download($adapter, $this->fileName($type, 'xlsx'));
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function exportForType(ReportType $type, array $filters, ReportQueryService $reports): ReportExport
    {
        return match ($type) {
            ReportType::IndicatorByPeriod => new IndicatorAchievementByPeriodExport($reports, $filters),
            ReportType::IndicatorByUnit => new IndicatorAchievementByUnitExport($reports, $filters),
            ReportType::LpmValidation => new LpmValidationExport($reports, $filters),
            ReportType::AmiByPeriod => new AmiAuditReportExport($reports, $filters),
            ReportType::AuditFindings => new AuditFindingReportExport($reports, $filters),
            ReportType::CorrectiveActions => new CorrectiveActionReportExport($reports, $filters),
        };
    }

    private function fileName(ReportType $type, string $extension): string
    {
        return $type->fileNamePrefix().'-'.now()->format('Ymd-His').'.'.$extension;
    }
}
