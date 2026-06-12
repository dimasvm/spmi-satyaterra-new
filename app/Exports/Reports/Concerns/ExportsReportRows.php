<?php

namespace App\Exports\Reports\Concerns;

use App\Services\Reports\ReportQueryService;
use Illuminate\Support\Collection;

trait ExportsReportRows
{
    public function __construct(
        protected ReportQueryService $reports,
        protected array $filters = [],
    ) {}

    public function rows(): Collection
    {
        return $this->reports->rows($this->type(), $this->filters)
            ->map(fn (array $row): array => array_values($row));
    }
}
