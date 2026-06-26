<?php

namespace App\Http\Controllers;

use App\Enums\AmiAuditStatus;
use App\Enums\AmiFindingCategory;
use App\Enums\AmiFindingStatus;
use App\Enums\IndicatorAssignmentStatus;
use App\Enums\ManagementReviewItemStatus;
use App\Enums\QualityStandardStatus;
use App\Enums\SpmiPeriodStatus;
use App\Enums\StandardImprovementProposalStatus;
use App\Models\AmiAudit;
use App\Models\AmiFinding;
use App\Models\CampusProfile;
use App\Models\IndicatorUnitAssignment;
use App\Models\ManagementReviewItem;
use App\Models\QualityStandard;
use App\Models\SpmiPeriod;
use App\Models\StandardImprovementProposal;
use App\Models\StandardIndicator;
use App\Models\StandardStatement;
use App\Models\Unit;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SpmiEfektivitasSiklusExportController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = Auth::user();

        // Check if user has permission to view dashboard
        abort_unless($user && $user->can('dashboard.view'), 403);

        $periodId = $request->query('spmi_period_id');
        $period = $periodId ? SpmiPeriod::find($periodId) : SpmiPeriod::active()->first();

        if (! $period) {
            $period = SpmiPeriod::latest('start_date')->first();
        }

        if (! $period) {
            abort(404, 'Periode SPMI tidak ditemukan.');
        }

        // Metadata
        $namaUniversitas = CampusProfile::getActive()?->name ?? 'Satyaterra';
        $tahunAkademik = $period->academic_year;
        $nomorSiklus = SpmiPeriod::where('start_date', '<=', $period->start_date)->count();
        $tanggalDihasilkanSistem = Carbon::now()->locale('id')->translatedFormat('d F Y');
        $namaKetuaLpm = User::role('admin_lpm')->first()?->name ?? 'Dr. Jane Doe, M.Pd.';

        $statusSiklus = match ($period->status) {
            SpmiPeriodStatus::Closed => 'SELESAI / SIKLUS DITUTUP',
            SpmiPeriodStatus::Active => 'AKTIF / SEDANG BERJALAN',
            SpmiPeriodStatus::Draft => 'DRAF / PERANCANGAN',
            SpmiPeriodStatus::Archived => 'DIARSIPKAN',
            default => strtoupper($period->status?->getLabel() ?? 'AKTIF'),
        };

        // --- P - Penetapan ---
        $tarStandar = QualityStandard::where('spmi_period_id', $period->id)->count();
        $realStandar = QualityStandard::where('spmi_period_id', $period->id)
            ->whereIn('status', [QualityStandardStatus::Active, QualityStandardStatus::Approved])
            ->count();
        $pctP1_1 = $tarStandar > 0 ? (int) round(($realStandar / $tarStandar) * 100) : 0;

        $tarPernyataan = StandardStatement::whereHas('qualityStandard', fn ($q) => $q->where('spmi_period_id', $period->id))->count();
        $realPernyataan = StandardStatement::whereHas('qualityStandard', fn ($q) => $q->where('spmi_period_id', $period->id)
            ->whereIn('status', [QualityStandardStatus::Active, QualityStandardStatus::Approved]))->count();
        $pctP1_2 = $tarPernyataan > 0 ? (int) round(($realPernyataan / $tarPernyataan) * 100) : 0;

        $tarIndikator = StandardIndicator::whereHas('qualityStandard', fn ($q) => $q->where('spmi_period_id', $period->id))->count();
        $realIndikator = StandardIndicator::whereHas('qualityStandard', fn ($q) => $q->where('spmi_period_id', $period->id)
            ->whereIn('status', [QualityStandardStatus::Active, QualityStandardStatus::Approved]))->count();
        $pctP1_3 = $tarIndikator > 0 ? (int) round(($realIndikator / $tarIndikator) * 100) : 0;

        // --- P - Pelaksanaan ---
        $totalUnit = Unit::where('is_active', true)->count();
        $unitSubmit = IndicatorUnitAssignment::where('spmi_period_id', $period->id)
            ->whereIn('status', [IndicatorAssignmentStatus::Submitted, IndicatorAssignmentStatus::Validated])
            ->distinct('unit_id')
            ->count('unit_id');
        $pctP2_1 = $totalUnit > 0 ? (int) round(($unitSubmit / $totalUnit) * 100) : 0;

        $totalReqBukti = IndicatorUnitAssignment::where('spmi_period_id', $period->id)->count();
        $realBuktiUp = IndicatorUnitAssignment::where('spmi_period_id', $period->id)
            ->whereIn('status', [IndicatorAssignmentStatus::Submitted, IndicatorAssignmentStatus::Validated])
            ->count();
        $pctP2_2 = $totalReqBukti > 0 ? (int) round(($realBuktiUp / $totalReqBukti) * 100) : 0;

        // --- E - Evaluasi ---
        $unitSelesaiAmi = AmiAudit::whereHas('amiPeriod', fn ($q) => $q->where('spmi_period_id', $period->id))
            ->whereIn('status', [AmiAuditStatus::Completed, AmiAuditStatus::Finalized])
            ->distinct('auditee_unit_id')
            ->count('auditee_unit_id');
        $pctE_1 = $totalUnit > 0 ? (int) round(($unitSelesaiAmi / $totalUnit) * 100) : 0;

        $totalKts = AmiFinding::whereHas('audit.amiPeriod', fn ($q) => $q->where('spmi_period_id', $period->id))
            ->whereIn('category', [AmiFindingCategory::Minor, AmiFindingCategory::Major])
            ->count();
        $totalOb = AmiFinding::whereHas('audit.amiPeriod', fn ($q) => $q->where('spmi_period_id', $period->id))
            ->whereIn('category', [AmiFindingCategory::Observation, AmiFindingCategory::Ofi])
            ->count();
        $sumTemuan = $totalKts + $totalOb;

        // --- P - Pengendalian ---
        $temuanClosed = AmiFinding::whereHas('audit.amiPeriod', fn ($q) => $q->where('spmi_period_id', $period->id))
            ->where('status', AmiFindingStatus::Closed)
            ->count();
        $pctP3_1 = $sumTemuan > 0 ? (int) round(($temuanClosed / $sumTemuan) * 100) : 0;

        $totalRekomendasi = ManagementReviewItem::whereHas('managementReview', fn ($q) => $q->where('spmi_period_id', $period->id))->count();
        $rekomDone = ManagementReviewItem::whereHas('managementReview', fn ($q) => $q->where('spmi_period_id', $period->id))
            ->where('status', ManagementReviewItemStatus::FollowedUp)
            ->count();
        $pctP3_2 = $totalRekomendasi > 0 ? (int) round(($rekomDone / $totalRekomendasi) * 100) : 0;

        // --- P - Peningkatan ---
        $rencanaUpgrade = StandardImprovementProposal::where('target_spmi_period_id', $period->id)
            ->whereIn('status', [StandardImprovementProposalStatus::Approved, StandardImprovementProposalStatus::Implemented])
            ->count();
        $realUpgrade = StandardImprovementProposal::where('target_spmi_period_id', $period->id)
            ->where('status', StandardImprovementProposalStatus::Implemented)
            ->count();
        $pctP4_1 = $rencanaUpgrade > 0 ? (int) round(($realUpgrade / $rencanaUpgrade) * 100) : 0;

        // Breakdown Per Standar Mutu
        $standardsQuery = QualityStandard::where('spmi_period_id', $period->id)
            ->with(['statements', 'indicators'])
            ->get();

        $standards = [];
        foreach ($standardsQuery as $std) {
            $stdSubmittedUnitsCount = IndicatorUnitAssignment::whereHas('standardIndicator', fn ($q) => $q->where('quality_standard_id', $std->id))
                ->where('spmi_period_id', $period->id)
                ->whereIn('status', [IndicatorAssignmentStatus::Submitted, IndicatorAssignmentStatus::Validated])
                ->distinct('unit_id')
                ->count('unit_id');

            $ratio = "{$stdSubmittedUnitsCount} / {$totalUnit}";

            $stdKts = AmiFinding::whereHas('standardIndicator', fn ($q) => $q->where('quality_standard_id', $std->id))
                ->whereHas('audit.amiPeriod', fn ($q) => $q->where('spmi_period_id', $period->id))
                ->whereIn('category', [AmiFindingCategory::Minor, AmiFindingCategory::Major])
                ->count();

            $stdOb = AmiFinding::whereHas('standardIndicator', fn ($q) => $q->where('quality_standard_id', $std->id))
                ->whereHas('audit.amiPeriod', fn ($q) => $q->where('spmi_period_id', $period->id))
                ->whereIn('category', [AmiFindingCategory::Observation, AmiFindingCategory::Ofi])
                ->count();

            $stdTotalFindings = $stdKts + $stdOb;
            $stdClosedFindings = AmiFinding::whereHas('standardIndicator', fn ($q) => $q->where('quality_standard_id', $std->id))
                ->whereHas('audit.amiPeriod', fn ($q) => $q->where('spmi_period_id', $period->id))
                ->where('status', AmiFindingStatus::Closed)
                ->count();

            $rtmPct = $stdTotalFindings > 0 ? (int) round(($stdClosedFindings / $stdTotalFindings) * 100) : 100;

            $subStandar = $std->statements->map(fn ($st) => $st->code)->implode(', ');
            if (empty($subStandar)) {
                $subStandar = '-';
            }

            $standards[] = [
                'kode_nama' => "{$std->code} - {$std->name}",
                'sub_standar' => $subStandar,
                'pernyataan_count' => $std->statements->count(),
                'indikator_count' => $std->indicators->count(),
                'submitted_units_ratio' => $ratio,
                'kts_count' => $stdKts,
                'ob_count' => $stdOb,
                'rtm_completion' => $rtmPct,
            ];
        }

        // Sort by standard code
        usort($standards, function (array $a, array $b): int {
            return strcmp($a['kode_nama'], $b['kode_nama']);
        });

        // Generate narrative conclusion
        $narasiKesimpulanOtomatisLpm = "Berdasarkan hasil analisis efektivitas siklus PPEPP pada Tahun Akademik {$tahunAkademik} (Siklus {$nomorSiklus}), disimpulkan bahwa secara makro tingkat pelaksanaan standar mutu berada pada rasio bukti fisik terunggah sebesar {$pctP2_2}% dari {$totalReqBukti} penugasan. Pada tahap Evaluasi, sebanyak {$unitSelesaiAmi} unit kerja telah diaudit melalui AMI ({$pctE_1}%), yang menghasilkan {$totalKts} temuan KTS dan {$totalOb} temuan Observasi. Seluruh temuan tersebut telah ditindaklanjuti pada tahap Pengendalian (RTM) dengan tingkat efektivitas penutupan temuan sebesar {$pctP3_1}%. Di sisi lain, rekomendasi RTM yang terealisasi mencapai {$pctP3_2}%. Untuk rencana peningkatan standar mutu (Upgrade), sebanyak {$rencanaUpgrade} standar telah diusulkan dengan tingkat implementasi sebesar {$pctP4_1}%. Lembaga Penjaminan Mutu merekomendasikan unit kerja untuk terus menjaga konsistensi pengisian dokumen mutu dan mempercepat penyelesaian rencana perbaikan yang telah disepakati.";

        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $pdf = Pdf::loadView('reports.pdf.spmi-efektivitas-siklus', [
            'namaUniversitas' => $namaUniversitas,
            'tahunAkademik' => $tahunAkademik,
            'nomorSiklus' => $nomorSiklus,
            'tanggalDihasilkanSistem' => $tanggalDihasilkanSistem,
            'namaKetuaLpm' => $namaKetuaLpm,
            'statusSiklus' => $statusSiklus,

            // P1
            'tarStandar' => $tarStandar,
            'realStandar' => $realStandar,
            'pctP1_1' => $pctP1_1,
            'tarPernyataan' => $tarPernyataan,
            'realPernyataan' => $realPernyataan,
            'pctP1_2' => $pctP1_2,
            'tarIndikator' => $tarIndikator,
            'realIndikator' => $realIndikator,
            'pctP1_3' => $pctP1_3,

            // P2
            'totalUnit' => $totalUnit,
            'unitSubmit' => $unitSubmit,
            'pctP2_1' => $pctP2_1,
            'totalReqBukti' => $totalReqBukti,
            'realBuktiUp' => $realBuktiUp,
            'pctP2_2' => $pctP2_2,

            // E
            'unitSelesaiAmi' => $unitSelesaiAmi,
            'pctE_1' => $pctE_1,
            'totalKts' => $totalKts,
            'totalOb' => $totalOb,
            'sumTemuan' => $sumTemuan,

            // P3
            'temuanClosed' => $temuanClosed,
            'pctP3_1' => $pctP3_1,
            'totalRekomendasi' => $totalRekomendasi,
            'rekomDone' => $rekomDone,
            'pctP3_2' => $pctP3_2,

            // P4
            'rencanaUpgrade' => $rencanaUpgrade,
            'realUpgrade' => $realUpgrade,
            'pctP4_1' => $pctP4_1,

            'standards' => $standards,
            'narasiKesimpulanOtomatisLpm' => $narasiKesimpulanOtomatisLpm,
        ]);

        $fileName = 'laporan-efektivitas-siklus-ppepp-'.now()->format('Ymd-His').'.pdf';

        return $pdf->download($fileName);
    }
}
