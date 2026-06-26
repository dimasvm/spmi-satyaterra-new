<?php

namespace App\Http\Controllers;

use App\Enums\AmiAuditorRole;
use App\Enums\AmiFindingCategory;
use App\Models\AmiAudit;
use App\Models\CampusProfile;
use App\Models\QualityStandard;
use App\Models\SpmiPeriod;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AmiAuditPdfExportController extends Controller
{
    public function __invoke(Request $request, AmiAudit $record): Response
    {
        $user = Auth::user();

        // 1. Authorize the user
        abort_unless($user && $user->can('ami-audits.export'), 403);

        // 2. Resolve the AmiAudit record scoped to user access
        $amiAudit = AmiAudit::forUser($user)
            ->with(['amiPeriod.spmiPeriod', 'auditeeUnit'])
            ->findOrFail($record->id);

        // 3. Metadata
        $namaUniversitas = CampusProfile::getActive()?->name ?? 'Satyaterra';
        $spmiPeriod = $amiAudit->amiPeriod?->spmiPeriod;
        $siklusAmi = $spmiPeriod ? SpmiPeriod::where('start_date', '<=', $spmiPeriod->start_date)->count() : '-';
        $tahunAmi = $spmiPeriod?->academic_year ?? '-';

        // 4. Identitas Auditee & Auditor
        $namaUnitKerja = $amiAudit->auditeeUnit?->name ?? '-';

        // Auditee PIC (user with role unit_pic mapped to this unit)
        $unitPic = User::where('unit_id', $amiAudit->auditee_unit_id)
            ->role('unit_pic')
            ->first();
        $namaKetuaUnitKerja = $unitPic?->name ?? 'Belum ditentukan';
        $jabatanKetuaUnitKerja = $unitPic ? 'Kepala '.($amiAudit->auditeeUnit?->name ?? 'Unit') : 'Kepala Unit Kerja';

        // Lead Auditor
        $leadAssignment = $amiAudit->auditorAssignments()
            ->where('role', AmiAuditorRole::Lead)
            ->with('user')
            ->first();
        $namaKetuaAuditor = $leadAssignment?->user?->name ?? 'Belum ditentukan';

        // Members & Observers
        $memberAssignments = $amiAudit->auditorAssignments()
            ->whereIn('role', [AmiAuditorRole::Member, AmiAuditorRole::Observer])
            ->with('user')
            ->get();
        $anggotaAuditors = $memberAssignments->pluck('user.name')->filter()->values()->all();

        $tanggalPelaksanaanAmi = $amiAudit->scheduled_date
            ? Carbon::parse($amiAudit->scheduled_date)->locale('id')->translatedFormat('d F Y')
            : '-';

        // 5. Lingkup Audit
        $qualityStandards = QualityStandard::whereIn('id', function ($query) use ($amiAudit) {
            $query->select('standard_indicators.quality_standard_id')
                ->from('ami_checklists')
                ->join('standard_indicators', 'ami_checklists.standard_indicator_id', '=', 'standard_indicators.id')
                ->where('ami_checklists.ami_audit_id', $amiAudit->id);
        })->distinct()->get();

        $lingkupStandarMutuAudit = $qualityStandards->map(fn ($qs) => "{$qs->code} - {$qs->name}")->implode(', ');
        if (empty($lingkupStandarMutuAudit)) {
            $lingkupStandarMutuAudit = 'Seluruh Standar Mutu yang Ditugaskan';
        }

        // 6. Temuan Ketidaksesuaian Audit (Section IV)
        $findings = $amiAudit->findings()
            ->with(['standardIndicator.qualityStandard'])
            ->where('category', '!=', AmiFindingCategory::Ofi)
            ->orderBy('category', 'desc')
            ->orderBy('id', 'asc')
            ->get();

        // 7. Peluang untuk Peningkatan (OFI) (Section V)
        $ofiFindings = $amiAudit->findings()
            ->with(['standardIndicator'])
            ->where('category', AmiFindingCategory::Ofi)
            ->orderBy('id', 'asc')
            ->get();

        // 8. Kesimpulan (Section VI)
        $kesimpulanUmumHasilAudit = $amiAudit->notes;
        if (empty($kesimpulanUmumHasilAudit)) {
            $kesimpulanUmumHasilAudit = 'Berdasarkan hasil audit mutu internal yang telah dilaksanakan pada unit kerja '.$namaUnitKerja.', secara umum pelaksanaan standar penjaminan mutu internal berjalan dengan baik. Beberapa temuan ketidaksesuaian (KTS) dan observasi (OB) yang teridentifikasi dalam audit ini diharapkan dapat menjadi perhatian bagi unit kerja untuk ditindaklanjuti dengan tindakan koreksi guna perbaikan berkelanjutan (continuous improvement).';
        }

        // Generate PDF
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $pdf = Pdf::loadView('reports.pdf.ami-audit-report', [
            'namaUniversitas' => $namaUniversitas,
            'siklusAmi' => $siklusAmi,
            'tahunAmi' => $tahunAmi,
            'namaUnitKerja' => $namaUnitKerja,
            'namaKetuaUnitKerja' => $namaKetuaUnitKerja,
            'jabatanKetuaUnitKerja' => $jabatanKetuaUnitKerja,
            'namaKetuaAuditor' => $namaKetuaAuditor,
            'anggotaAuditors' => $anggotaAuditors,
            'tanggalPelaksanaanAmi' => $tanggalPelaksanaanAmi,
            'lingkupStandarMutuAudit' => $lingkupStandarMutuAudit,
            'findings' => $findings,
            'ofiFindings' => $ofiFindings,
            'kesimpulanUmumHasilAudit' => $kesimpulanUmumHasilAudit,
        ]);

        $unitCode = $amiAudit->auditeeUnit?->code ?: 'UNIT';
        $fileName = 'laporan-ami-'.strtolower($unitCode).'-'.now()->format('Ymd-His').'.pdf';

        return $pdf->download($fileName);
    }
}
