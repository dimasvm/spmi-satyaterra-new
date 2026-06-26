<?php

namespace App\Http\Controllers;

use App\Models\IndicatorUnitAssignment;
use App\Models\SpmiPeriod;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SpmiDashboardExportController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = Auth::user();

        // Check if user has permission to view dashboard
        abort_unless($user && $user->can('dashboard.view'), 403);

        $periodId = $request->query('spmi_period_id');
        $period = $periodId ? SpmiPeriod::find($periodId) : SpmiPeriod::active()->first();

        if (! $period) {
            $period = SpmiPeriod::latest()->first();
        }

        if (! $period) {
            abort(404, 'Periode SPMI tidak ditemukan.');
        }

        // Get assignments scoped to the user and period
        $assignments = IndicatorUnitAssignment::query()
            ->forUser($user)
            ->where('spmi_period_id', $period->id)
            ->with([
                'unit',
                'standardIndicator.qualityStandard',
                'latestAchievement',
            ])
            ->get();

        // Group by unit_id and quality_standard_id
        $grouped = $assignments->groupBy(function (IndicatorUnitAssignment $assignment) {
            return $assignment->unit_id.'_'.($assignment->standardIndicator?->quality_standard_id ?? 0);
        });

        $rows = [];
        $statusSubmit = 0;
        $statusDraft = 0;
        $statusBelum = 0;

        foreach ($grouped as $key => $group) {
            $first = $group->first();
            if (! $first || ! $first->standardIndicator || ! $first->standardIndicator->qualityStandard) {
                continue;
            }

            $unitName = $first->unit?->name ?? '-';
            $standardName = $first->standardIndicator->qualityStandard->name ?? '-';

            // Get individual statuses in the group
            $statuses = $group->map(function ($assignment) {
                return $assignment->status?->value ?? $assignment->status;
            })->toArray();

            // Row status logic:
            // SUBMIT: All assignments are validated or submitted
            // DRAFT: Any assignment is returned or in_progress, or there's a mix of submitted/assigned
            // Belum Mengisi: All assignments are assigned (not started)
            if (collect($statuses)->every(fn ($s) => in_array($s, ['submitted', 'validated'], true))) {
                $rowStatus = 'SUBMIT';
                $statusSubmit++;
            } elseif (collect($statuses)->every(fn ($s) => $s === 'assigned')) {
                $rowStatus = 'BELUM';
                $statusBelum++;
            } else {
                $rowStatus = 'DRAFT';
                $statusDraft++;
            }

            // Ratio of filled indicators (submitted/validated) over total indicators in standard
            $totalInStandard = $group->count();
            $filledInStandard = $group->filter(fn ($a) => in_array($a->status?->value ?? $a->status, ['submitted', 'validated'], true))->count();
            $ratio = "{$filledInStandard}/{$totalInStandard}";

            // Concatenate notes
            $notesList = [];
            foreach ($group as $assignment) {
                if (filled($assignment->notes)) {
                    $notesList[] = $assignment->notes;
                }
                if ($assignment->latestAchievement && filled($assignment->latestAchievement->notes)) {
                    $notesList[] = $assignment->latestAchievement->notes;
                }
            }
            $notes = count($notesList) > 0 ? implode('; ', array_unique($notesList)) : '-';

            $rows[] = [
                'unit_kerja' => $unitName,
                'nama_standar' => $standardName,
                'status_pengisian' => $rowStatus,
                'rasio_bukti' => $ratio,
                'catatan' => $notes,
            ];
        }

        // Sort rows by unit work then by standard name
        usort($rows, function ($a, $b) {
            $unitCompare = strcmp($a['unit_kerja'], $b['unit_kerja']);
            if ($unitCompare !== 0) {
                return $unitCompare;
            }

            return strcmp($a['nama_standar'], $b['nama_standar']);
        });

        // Aggregate statistics for Executive Summary
        $totalUnits = $assignments->pluck('unit_id')->filter()->unique()->count();
        $totalStandards = count($rows);

        // Metadata details
        $periodName = $period->name;
        $exportDate = Carbon::now()->locale('id')->translatedFormat('d F Y');
        $syncTime = Carbon::now()->format('H:i').' WIB';
        $picName = $user->name;

        // Generate PDF
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $pdf = Pdf::loadView('reports.pdf.spmi-dashboard-monitoring', [
            'periodName' => $periodName,
            'exportDate' => $exportDate,
            'syncTime' => $syncTime,
            'picName' => $picName,
            'totalUnits' => $totalUnits,
            'totalStandards' => $totalStandards,
            'statusSubmit' => $statusSubmit,
            'statusDraft' => $statusDraft,
            'statusBelum' => $statusBelum,
            'rows' => $rows,
        ]);

        $fileName = 'laporan-monitoring-progres-pelaksanaan-standar-'.now()->format('Ymd-His').'.pdf';

        return $pdf->download($fileName);
    }
}
