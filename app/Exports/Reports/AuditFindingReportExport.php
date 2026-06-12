<?php

namespace App\Exports\Reports;

use App\Enums\ReportType;
use App\Exports\Reports\Concerns\ExportsReportRows;

class AuditFindingReportExport implements ReportExport
{
    use ExportsReportRows;

    public function headings(): array
    {
        return $this->reports->headings($this->type());
    }

    private function type(): ReportType
    {
        return ReportType::AuditFindings;
    }
}
