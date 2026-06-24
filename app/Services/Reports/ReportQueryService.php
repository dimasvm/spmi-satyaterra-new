<?php

namespace App\Services\Reports;

use App\Enums\AmiAssessmentResult;
use App\Enums\ReportType;
use App\Models\AmiAudit;
use App\Models\AmiFinding;
use App\Models\CorrectiveAction;
use App\Models\IndicatorAchievement;
use App\Models\ManagementReview;
use App\Models\StandardImprovementProposal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ReportQueryService
{
    /**
     * @return array<int, string>
     */
    public function headings(ReportType $type): array
    {
        return match ($type) {
            ReportType::IndicatorByPeriod, ReportType::IndicatorByUnit, ReportType::LpmValidation => [
                'Periode',
                'Unit',
                'Standar',
                'Indikator',
                'Target',
                'Realisasi',
                'Status Capaian',
                'Status Validasi',
                'Catatan',
                'Jumlah Bukti',
            ],
            ReportType::AmiByPeriod => [
                'Periode AMI',
                'Unit Auditee',
                'Tanggal Audit',
                'Auditor',
                'Jumlah Checklist',
                'Jumlah Sesuai',
                'Jumlah Observasi',
                'Jumlah Minor',
                'Jumlah Mayor',
                'Jumlah OFI',
                'Status Audit',
            ],
            ReportType::AuditFindings => [
                'Nomor Temuan',
                'Unit',
                'Indikator',
                'Kategori',
                'Deskripsi',
                'Rekomendasi',
                'Deadline',
                'Status',
            ],
            ReportType::CorrectiveActions => [
                'Nomor Temuan',
                'Unit',
                'Rencana Tindakan',
                'PIC',
                'Target Selesai',
                'Status',
                'Status Verifikasi',
                'Catatan Reviewer',
            ],
            ReportType::ManagementReviews => [
                'Periode SPMI',
                'Periode AMI',
                'Judul RTM',
                'Tanggal Rapat',
                'Lokasi',
                'Jumlah Keputusan',
                'Jumlah Usulan',
                'Status',
            ],
            ReportType::StandardImprovements => [
                'Periode Target',
                'RTM',
                'Standar',
                'Indikator',
                'Jenis Usulan',
                'Judul Usulan',
                'Status',
                'Pengusul',
                'Tanggal Review',
            ],
        };
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, array<string, mixed>>
     */
    public function rows(ReportType $type, array $filters): Collection
    {
        return match ($type) {
            ReportType::IndicatorByPeriod, ReportType::IndicatorByUnit => $this->indicatorAchievementRows($filters),
            ReportType::LpmValidation => $this->lpmValidationRows($filters),
            ReportType::AmiByPeriod => $this->amiAuditRows($filters),
            ReportType::AuditFindings => $this->auditFindingRows($filters),
            ReportType::CorrectiveActions => $this->correctiveActionRows($filters),
            ReportType::ManagementReviews => $this->managementReviewRows($filters),
            ReportType::StandardImprovements => $this->standardImprovementRows($filters),
        };
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, array<string, mixed>>
     */
    private function indicatorAchievementRows(array $filters): Collection
    {
        return $this->indicatorAchievementQuery($filters)
            ->get()
            ->map(function (IndicatorAchievement $achievement): array {
                $assignment = $achievement->assignment;
                $indicator = $assignment?->standardIndicator;
                $standard = $indicator?->qualityStandard;

                return [
                    'periode' => $assignment?->spmiPeriod?->name ?? '-',
                    'unit' => $assignment?->unit?->name ?? '-',
                    'standar' => $standard?->name ?? '-',
                    'indikator' => trim(($indicator?->code ? $indicator->code.' - ' : '').($indicator?->statement ?? '-')),
                    'target' => $this->targetSummary($indicator),
                    'realisasi' => $achievement->realization_value ?? $achievement->realization_text ?? '-',
                    'status_capaian' => $achievement->achievement_status?->getLabel() ?? '-',
                    'status_validasi' => $achievement->submission_status?->getLabel() ?? '-',
                    'catatan' => $achievement->notes ?? $achievement->latestReview?->notes ?? '-',
                    'jumlah_bukti' => $achievement->evidences_count,
                ];
            });
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, array<string, mixed>>
     */
    private function lpmValidationRows(array $filters): Collection
    {
        return $this->indicatorAchievementRows($filters);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, array<string, mixed>>
     */
    private function amiAuditRows(array $filters): Collection
    {
        return $this->amiAuditQuery($filters)
            ->withCount([
                'checklists',
                'checklists as conform_count' => fn (Builder $query): Builder => $query->where('assessment_result', AmiAssessmentResult::Conform->value),
                'checklists as observation_count' => fn (Builder $query): Builder => $query->where('assessment_result', AmiAssessmentResult::Observation->value),
                'checklists as minor_count' => fn (Builder $query): Builder => $query->where('assessment_result', AmiAssessmentResult::Minor->value),
                'checklists as major_count' => fn (Builder $query): Builder => $query->where('assessment_result', AmiAssessmentResult::Major->value),
                'checklists as ofi_count' => fn (Builder $query): Builder => $query->where('assessment_result', AmiAssessmentResult::Ofi->value),
            ])
            ->get()
            ->map(fn (AmiAudit $audit): array => [
                'periode_ami' => $audit->amiPeriod?->name ?? '-',
                'unit_auditee' => $audit->auditeeUnit?->name ?? '-',
                'tanggal_audit' => $audit->scheduled_date?->format('d M Y') ?? '-',
                'auditor' => $audit->auditors->pluck('name')->join(', ') ?: '-',
                'jumlah_checklist' => $audit->checklists_count,
                'jumlah_sesuai' => $audit->conform_count,
                'jumlah_observasi' => $audit->observation_count,
                'jumlah_minor' => $audit->minor_count,
                'jumlah_mayor' => $audit->major_count,
                'jumlah_ofi' => $audit->ofi_count,
                'status_audit' => $audit->status?->getLabel() ?? '-',
            ]);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, array<string, mixed>>
     */
    private function auditFindingRows(array $filters): Collection
    {
        return $this->auditFindingQuery($filters)
            ->get()
            ->map(fn (AmiFinding $finding): array => [
                'nomor_temuan' => $finding->finding_number ?? '-',
                'unit' => $finding->audit?->auditeeUnit?->name ?? '-',
                'indikator' => $finding->standardIndicator?->code ?? '-',
                'kategori' => $finding->category?->getLabel() ?? '-',
                'deskripsi' => $finding->description,
                'rekomendasi' => $finding->recommendation ?? '-',
                'deadline' => $finding->due_date?->format('d M Y') ?? '-',
                'status' => $finding->status?->getLabel() ?? '-',
            ]);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, array<string, mixed>>
     */
    private function correctiveActionRows(array $filters): Collection
    {
        return $this->correctiveActionQuery($filters)
            ->get()
            ->map(fn (CorrectiveAction $action): array => [
                'nomor_temuan' => $action->finding?->finding_number ?? '-',
                'unit' => $action->finding?->audit?->auditeeUnit?->name ?? '-',
                'rencana_tindakan' => $action->action_plan,
                'pic' => $action->picUser?->name ?? '-',
                'target_selesai' => $action->target_date?->format('d M Y') ?? '-',
                'status' => $action->status?->getLabel() ?? '-',
                'status_verifikasi' => $action->latestReview?->status?->getLabel() ?? '-',
                'catatan_reviewer' => $action->latestReview?->notes ?? '-',
            ]);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, array<string, mixed>>
     */
    private function managementReviewRows(array $filters): Collection
    {
        return $this->managementReviewQuery($filters)
            ->withCount(['items', 'improvementProposals'])
            ->get()
            ->map(fn (ManagementReview $review): array => [
                'periode_spmi' => $review->spmiPeriod?->name ?? '-',
                'periode_ami' => $review->amiPeriod?->name ?? '-',
                'judul_rtm' => $review->title,
                'tanggal_rapat' => $review->meeting_date?->format('d M Y') ?? '-',
                'lokasi' => $review->location ?? '-',
                'jumlah_keputusan' => $review->items_count,
                'jumlah_usulan' => $review->improvement_proposals_count,
                'status' => $review->status?->getLabel() ?? '-',
            ]);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, array<string, mixed>>
     */
    private function standardImprovementRows(array $filters): Collection
    {
        return $this->standardImprovementQuery($filters)
            ->get()
            ->map(fn (StandardImprovementProposal $proposal): array => [
                'periode_target' => $proposal->targetSpmiPeriod?->name ?? '-',
                'rtm' => $proposal->managementReview?->title ?? '-',
                'standar' => $proposal->qualityStandard?->name ?? '-',
                'indikator' => $proposal->standardIndicator?->code ?? '-',
                'jenis_usulan' => $proposal->proposal_type?->getLabel() ?? '-',
                'judul_usulan' => $proposal->title,
                'status' => $proposal->status?->getLabel() ?? '-',
                'pengusul' => $proposal->proposedBy?->name ?? '-',
                'tanggal_review' => $proposal->reviewed_at?->format('d M Y') ?? '-',
            ]);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function indicatorAchievementQuery(array $filters): Builder
    {
        $query = IndicatorAchievement::query()
            ->with([
                'assignment.spmiPeriod',
                'assignment.unit',
                'assignment.standardIndicator.qualityStandard.category.parent',
                'latestReview',
            ])
            ->withCount('evidences');

        $this->scopeUnitData($query);

        return $query
            ->when($filters['spmi_period_id'] ?? null, fn (Builder $query, int|string $id): Builder => $query->whereHas('assignment', fn (Builder $assignmentQuery): Builder => $assignmentQuery->where('spmi_period_id', $id)))
            ->when($filters['unit_id'] ?? null, fn (Builder $query, int|string $id): Builder => $query->whereHas('assignment', fn (Builder $assignmentQuery): Builder => $assignmentQuery->where('unit_id', $this->authorizedUnitId((int) $id))))
            ->when($filters['standard_category_id'] ?? null, fn (Builder $query, int|string $id): Builder => $query->whereHas('assignment.standardIndicator.qualityStandard', fn (Builder $standardQuery): Builder => $standardQuery->forStandardCategory($id)))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status): Builder => $query->where('submission_status', $status))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '>=', $date))
            ->when($filters['date_until'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '<=', $date))
            ->latest();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function amiAuditQuery(array $filters): Builder
    {
        $query = AmiAudit::query()
            ->with(['amiPeriod', 'auditeeUnit', 'auditors']);

        $this->scopeAmiData($query);

        return $query
            ->when($filters['ami_period_id'] ?? null, fn (Builder $query, int|string $id): Builder => $query->where('ami_period_id', $id))
            ->when($filters['unit_id'] ?? null, fn (Builder $query, int|string $id): Builder => $query->where('auditee_unit_id', $this->authorizedUnitId((int) $id)))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status): Builder => $query->where('status', $status))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('scheduled_date', '>=', $date))
            ->when($filters['date_until'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('scheduled_date', '<=', $date))
            ->latest('scheduled_date');
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function auditFindingQuery(array $filters): Builder
    {
        $query = AmiFinding::query()
            ->with(['audit.amiPeriod', 'audit.auditeeUnit', 'standardIndicator']);

        $this->scopeAmiRelationData($query, 'audit');

        return $query
            ->when($filters['ami_period_id'] ?? null, fn (Builder $query, int|string $id): Builder => $query->whereHas('audit', fn (Builder $auditQuery): Builder => $auditQuery->where('ami_period_id', $id)))
            ->when($filters['unit_id'] ?? null, fn (Builder $query, int|string $id): Builder => $query->whereHas('audit', fn (Builder $auditQuery): Builder => $auditQuery->where('auditee_unit_id', $this->authorizedUnitId((int) $id))))
            ->when($filters['standard_category_id'] ?? null, fn (Builder $query, int|string $id): Builder => $query->whereHas('standardIndicator.qualityStandard', fn (Builder $standardQuery): Builder => $standardQuery->forStandardCategory($id)))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status): Builder => $query->where('status', $status))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('due_date', '>=', $date))
            ->when($filters['date_until'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('due_date', '<=', $date))
            ->latest();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function correctiveActionQuery(array $filters): Builder
    {
        $query = CorrectiveAction::query()
            ->with(['finding.audit.auditeeUnit', 'picUser', 'latestReview']);

        $this->scopeAmiRelationData($query, 'finding.audit');

        return $query
            ->when($filters['ami_period_id'] ?? null, fn (Builder $query, int|string $id): Builder => $query->whereHas('finding.audit', fn (Builder $auditQuery): Builder => $auditQuery->where('ami_period_id', $id)))
            ->when($filters['unit_id'] ?? null, fn (Builder $query, int|string $id): Builder => $query->whereHas('finding.audit', fn (Builder $auditQuery): Builder => $auditQuery->where('auditee_unit_id', $this->authorizedUnitId((int) $id))))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status): Builder => $query->where('status', $status))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('target_date', '>=', $date))
            ->when($filters['date_until'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('target_date', '<=', $date))
            ->latest();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function managementReviewQuery(array $filters): Builder
    {
        $query = ManagementReview::query()
            ->with(['spmiPeriod', 'amiPeriod']);

        if ($user = auth()->user()) {
            $query->forUser($user);
        } else {
            $query->whereRaw('1 = 0');
        }

        return $query
            ->when($filters['spmi_period_id'] ?? null, fn (Builder $query, int|string $id): Builder => $query->where('spmi_period_id', $id))
            ->when($filters['ami_period_id'] ?? null, fn (Builder $query, int|string $id): Builder => $query->where('ami_period_id', $id))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status): Builder => $query->where('status', $status))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('meeting_date', '>=', $date))
            ->when($filters['date_until'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('meeting_date', '<=', $date))
            ->latest('meeting_date');
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function standardImprovementQuery(array $filters): Builder
    {
        $query = StandardImprovementProposal::query()
            ->with([
                'managementReview',
                'qualityStandard',
                'standardIndicator',
                'targetSpmiPeriod',
                'proposedBy',
            ]);

        if ($user = auth()->user()) {
            $query->forUser($user);
        } else {
            $query->whereRaw('1 = 0');
        }

        return $query
            ->when($filters['spmi_period_id'] ?? null, fn (Builder $query, int|string $id): Builder => $query->where('target_spmi_period_id', $id))
            ->when($filters['ami_period_id'] ?? null, fn (Builder $query, int|string $id): Builder => $query->whereHas('managementReview', fn (Builder $reviewQuery): Builder => $reviewQuery->where('ami_period_id', $id)))
            ->when($filters['unit_id'] ?? null, fn (Builder $query, int|string $id): Builder => $query->whereHas('managementReview.amiPeriod.audits', fn (Builder $auditQuery): Builder => $auditQuery->where('auditee_unit_id', $this->authorizedUnitId((int) $id))))
            ->when($filters['standard_category_id'] ?? null, fn (Builder $query, int|string $id): Builder => $query->whereHas('qualityStandard', fn (Builder $standardQuery): Builder => $standardQuery->forStandardCategory($id)))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status): Builder => $query->where('status', $status))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '>=', $date))
            ->when($filters['date_until'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '<=', $date))
            ->latest();
    }

    private function targetSummary(?object $indicator): string
    {
        if ($indicator === null) {
            return '-';
        }

        return trim(implode(' ', array_filter([
            $indicator->target_operator?->value,
            $indicator->target_value,
            $indicator->target_unit,
        ]))) ?: '-';
    }

    private function scopeUnitData(Builder $query): void
    {
        $user = auth()->user();

        if ($user?->hasRole('unit_pic') && $user->unit_id !== null) {
            $query->whereHas('assignment', fn (Builder $assignmentQuery): Builder => $assignmentQuery->where('unit_id', $user->unit_id));
        }

        if (! $user?->hasAnyRole(['super_admin', 'admin_lpm', 'pimpinan', 'unit_pic'])) {
            $query->whereRaw('1 = 0');
        }
    }

    private function scopeAmiData(Builder $query): void
    {
        $user = auth()->user();

        if ($user?->hasAnyRole(['super_admin', 'admin_lpm', 'pimpinan'])) {
            return;
        }

        if ($user?->hasRole('unit_pic') && $user->unit_id !== null) {
            $query->where('auditee_unit_id', $user->unit_id);

            return;
        }

        if ($user?->hasRole('auditor')) {
            $query->whereHas('auditorAssignments', fn (Builder $auditorQuery): Builder => $auditorQuery->where('user_id', $user->id));

            return;
        }

        $query->whereRaw('1 = 0');
    }

    private function scopeAmiRelationData(Builder $query, string $relation): void
    {
        $user = auth()->user();

        if ($user?->hasAnyRole(['super_admin', 'admin_lpm', 'pimpinan'])) {
            return;
        }

        if ($user?->hasRole('unit_pic') && $user->unit_id !== null) {
            $query->whereHas($relation, fn (Builder $auditQuery): Builder => $auditQuery->where('auditee_unit_id', $user->unit_id));

            return;
        }

        if ($user?->hasRole('auditor')) {
            $query->whereHas($relation.'.auditorAssignments', fn (Builder $auditorQuery): Builder => $auditorQuery->where('user_id', $user->id));

            return;
        }

        $query->whereRaw('1 = 0');
    }

    private function authorizedUnitId(int $unitId): int
    {
        $user = auth()->user();

        if ($user?->hasRole('unit_pic') && $user->unit_id !== null) {
            return $user->unit_id;
        }

        return $unitId;
    }
}
