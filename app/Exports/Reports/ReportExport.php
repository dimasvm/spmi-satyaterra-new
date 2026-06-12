<?php

namespace App\Exports\Reports;

use Illuminate\Support\Collection;

interface ReportExport
{
    /**
     * @return array<int, string>
     */
    public function headings(): array;

    /**
     * @return Collection<int, array<int, mixed>>
     */
    public function rows(): Collection;
}
