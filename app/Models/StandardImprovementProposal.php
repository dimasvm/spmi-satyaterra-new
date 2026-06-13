<?php

namespace App\Models;

use App\Enums\QualityStandardStatus;
use App\Enums\StandardImprovementProposalStatus;
use App\Enums\StandardImprovementProposalType;
use App\Enums\StandardIndicatorType;
use App\Enums\StandardRevisionType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StandardImprovementProposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'management_review_id',
        'quality_standard_id',
        'standard_indicator_id',
        'proposal_type',
        'title',
        'background',
        'current_condition',
        'proposed_change',
        'reason',
        'expected_impact',
        'proposed_target_value',
        'proposed_target_operator',
        'proposed_target_unit',
        'proposed_indicator_statement',
        'proposed_standard_name',
        'proposed_standard_description',
        'target_spmi_period_id',
        'status',
        'proposed_by',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'implemented_by',
        'implemented_at',
        'created_standard_id',
        'created_indicator_id',
    ];

    protected $casts = [
        'proposal_type' => StandardImprovementProposalType::class,
        'status' => StandardImprovementProposalStatus::class,
        'proposed_target_value' => 'decimal:2',
        'reviewed_at' => 'datetime',
        'implemented_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (StandardImprovementProposal $proposal): void {
            $proposal->status ??= StandardImprovementProposalStatus::Draft;
        });
    }

    public function managementReview(): BelongsTo
    {
        return $this->belongsTo(ManagementReview::class);
    }

    public function qualityStandard(): BelongsTo
    {
        return $this->belongsTo(QualityStandard::class);
    }

    public function standardIndicator(): BelongsTo
    {
        return $this->belongsTo(StandardIndicator::class);
    }

    public function targetSpmiPeriod(): BelongsTo
    {
        return $this->belongsTo(SpmiPeriod::class, 'target_spmi_period_id');
    }

    public function proposedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proposed_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function implementedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'implemented_by');
    }

    public function createdStandard(): BelongsTo
    {
        return $this->belongsTo(QualityStandard::class, 'created_standard_id');
    }

    public function createdIndicator(): BelongsTo
    {
        return $this->belongsTo(StandardIndicator::class, 'created_indicator_id');
    }

    public function revisionHistories(): HasMany
    {
        return $this->hasMany(StandardRevisionHistory::class);
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        if ($user->hasAnyRole(['super_admin', 'admin_lpm', 'pimpinan', 'viewer'])) {
            return $query;
        }

        if ($user->isAuditor()) {
            return $query->whereHas('managementReview.amiPeriod.audits.auditorAssignments', fn (Builder $auditorQuery): Builder => $auditorQuery
                ->where('user_id', $user->id));
        }

        if ($user->isUnitPic() && $user->unit_id !== null) {
            return $query->whereHas('managementReview.amiPeriod.audits', fn (Builder $auditQuery): Builder => $auditQuery
                ->where('auditee_unit_id', $user->unit_id));
        }

        return $query->whereRaw('1 = 0');
    }

    public function submit(User $user): void
    {
        if ($this->status !== StandardImprovementProposalStatus::Draft) {
            return;
        }

        $this->forceFill([
            'status' => StandardImprovementProposalStatus::Submitted,
            'proposed_by' => $this->proposed_by ?? $user->id,
        ])->save();
    }

    public function approve(User $user, ?string $notes = null): void
    {
        if ($this->status !== StandardImprovementProposalStatus::Submitted) {
            return;
        }

        $this->forceFill([
            'status' => StandardImprovementProposalStatus::Approved,
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ])->save();
    }

    public function reject(User $user, string $notes): void
    {
        if ($this->status !== StandardImprovementProposalStatus::Submitted) {
            return;
        }

        $this->forceFill([
            'status' => StandardImprovementProposalStatus::Rejected,
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ])->save();
    }

    public function implement(User $user): void
    {
        if ($this->status !== StandardImprovementProposalStatus::Approved) {
            return;
        }

        DB::transaction(function () use ($user): void {
            $this->refresh();

            match ($this->proposal_type) {
                StandardImprovementProposalType::CreateNewStandard => $this->implementNewStandard($user),
                StandardImprovementProposalType::ReviseStandard => $this->implementStandardRevision($user),
                StandardImprovementProposalType::CreateNewIndicator => $this->implementNewIndicator($user),
                StandardImprovementProposalType::ReviseIndicator => $this->implementIndicatorRevision($user),
                StandardImprovementProposalType::ReviseTarget => $this->implementTargetRevision($user),
                StandardImprovementProposalType::RemoveIndicator => $this->implementIndicatorRemovalNote($user),
                StandardImprovementProposalType::ReviseDocument => $this->implementDocumentRevisionNote($user),
            };

            $this->forceFill([
                'status' => StandardImprovementProposalStatus::Implemented,
                'implemented_by' => $user->id,
                'implemented_at' => now(),
            ])->save();
        });
    }

    private function implementNewStandard(User $user): void
    {
        $standard = QualityStandard::query()->create([
            'standard_category_id' => $this->defaultStandardCategoryId(),
            'spmi_period_id' => $this->target_spmi_period_id,
            'code' => $this->nextStandardCode(),
            'name' => $this->proposed_standard_name ?: $this->title,
            'description' => $this->proposed_standard_description ?: $this->proposed_change,
            'status' => QualityStandardStatus::Draft,
            'version' => 1,
        ]);

        $this->forceFill(['created_standard_id' => $standard->id])->save();

        $this->recordRevision(
            user: $user,
            revisionType: StandardRevisionType::NewStandard,
            qualityStandard: $standard,
            newData: $standard->only(['id', 'code', 'name', 'description', 'status', 'version']),
        );
    }

    private function implementStandardRevision(User $user): void
    {
        $standard = $this->qualityStandard;

        if ($standard === null) {
            throw new RuntimeException('Standar terkait belum dipilih.');
        }

        $oldData = $standard->only(['id', 'code', 'name', 'description', 'status', 'version']);

        $standard->forceFill([
            'name' => $this->proposed_standard_name ?: $standard->name,
            'description' => $this->proposed_standard_description ?: $this->proposed_change,
            'status' => QualityStandardStatus::Revised,
            'version' => ((int) $standard->version) + 1,
        ])->save();

        $this->recordRevision(
            user: $user,
            revisionType: StandardRevisionType::StandardRevision,
            qualityStandard: $standard,
            oldData: $oldData,
            newData: $standard->fresh()?->only(['id', 'code', 'name', 'description', 'status', 'version']),
        );
    }

    private function implementNewIndicator(User $user): void
    {
        $standard = $this->qualityStandard;

        if ($standard === null) {
            throw new RuntimeException('Standar terkait belum dipilih.');
        }

        $indicator = StandardIndicator::query()->create([
            'quality_standard_id' => $standard->id,
            'code' => $this->nextIndicatorCode($standard),
            'statement' => $this->proposed_indicator_statement ?: $this->proposed_change,
            'indicator_type' => $this->proposed_target_value === null
                ? StandardIndicatorType::Text
                : StandardIndicatorType::Percentage,
            'target_value' => $this->proposed_target_value,
            'target_operator' => $this->proposed_target_operator,
            'target_unit' => $this->proposed_target_unit,
            'weight' => 1,
            'evidence_required' => true,
        ]);

        $this->forceFill(['created_indicator_id' => $indicator->id])->save();

        $this->recordRevision(
            user: $user,
            revisionType: StandardRevisionType::NewIndicator,
            qualityStandard: $standard,
            standardIndicator: $indicator,
            newData: $indicator->only(['id', 'code', 'statement', 'indicator_type', 'target_value', 'target_operator', 'target_unit']),
        );
    }

    private function implementIndicatorRevision(User $user): void
    {
        $indicator = $this->standardIndicator;

        if ($indicator === null) {
            throw new RuntimeException('Indikator terkait belum dipilih.');
        }

        $oldData = $indicator->only(['id', 'code', 'statement', 'indicator_type', 'target_value', 'target_operator', 'target_unit']);

        $indicator->forceFill([
            'statement' => $this->proposed_indicator_statement ?: $this->proposed_change,
        ])->save();

        $this->recordRevision(
            user: $user,
            revisionType: StandardRevisionType::IndicatorRevision,
            qualityStandard: $indicator->qualityStandard,
            standardIndicator: $indicator,
            oldData: $oldData,
            newData: $indicator->fresh()?->only(['id', 'code', 'statement', 'indicator_type', 'target_value', 'target_operator', 'target_unit']),
        );
    }

    private function implementTargetRevision(User $user): void
    {
        $indicator = $this->standardIndicator;

        if ($indicator === null) {
            throw new RuntimeException('Indikator terkait belum dipilih.');
        }

        $oldData = $indicator->only(['id', 'code', 'target_value', 'target_operator', 'target_unit']);

        $indicator->forceFill([
            'target_value' => $this->proposed_target_value,
            'target_operator' => $this->proposed_target_operator,
            'target_unit' => $this->proposed_target_unit,
        ])->save();

        $this->recordRevision(
            user: $user,
            revisionType: StandardRevisionType::TargetRevision,
            qualityStandard: $indicator->qualityStandard,
            standardIndicator: $indicator,
            oldData: $oldData,
            newData: $indicator->fresh()?->only(['id', 'code', 'target_value', 'target_operator', 'target_unit']),
        );
    }

    private function implementIndicatorRemovalNote(User $user): void
    {
        $indicator = $this->standardIndicator;

        if ($indicator === null) {
            throw new RuntimeException('Indikator terkait belum dipilih.');
        }

        $this->recordRevision(
            user: $user,
            revisionType: StandardRevisionType::IndicatorRevision,
            qualityStandard: $indicator->qualityStandard,
            standardIndicator: $indicator,
            oldData: $indicator->only(['id', 'code', 'statement']),
            notes: 'TODO: tambahkan kolom is_active/status pada standard_indicators jika penghapusan indikator perlu diproses sebagai nonaktif.',
        );
    }

    private function implementDocumentRevisionNote(User $user): void
    {
        $this->recordRevision(
            user: $user,
            revisionType: StandardRevisionType::StandardRevision,
            qualityStandard: $this->qualityStandard,
            standardIndicator: $this->standardIndicator,
            notes: 'Usulan revisi dokumen dicatat dari RTM. Implementasi dokumen mengikuti modul Dokumen Mutu.',
        );
    }

    private function recordRevision(
        User $user,
        StandardRevisionType $revisionType,
        ?QualityStandard $qualityStandard = null,
        ?StandardIndicator $standardIndicator = null,
        ?array $oldData = null,
        ?array $newData = null,
        ?string $notes = null,
    ): void {
        $this->revisionHistories()->create([
            'quality_standard_id' => $qualityStandard?->id,
            'standard_indicator_id' => $standardIndicator?->id,
            'revision_type' => $revisionType,
            'old_data' => $oldData,
            'new_data' => $newData,
            'notes' => $notes,
            'revised_by' => $user->id,
            'revised_at' => now(),
        ]);
    }

    private function defaultStandardCategoryId(): int
    {
        $categoryId = $this->qualityStandard?->standard_category_id
            ?? StandardCategory::query()->value('id');

        if ($categoryId === null) {
            $categoryId = StandardCategory::query()->create([
                'code' => 'RTM',
                'name' => 'Peningkatan Standar',
                'description' => 'Kategori otomatis untuk standar hasil RTM.',
            ])->id;
        }

        return (int) $categoryId;
    }

    private function nextStandardCode(): string
    {
        return 'RTM-STD-'.str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
    }

    private function nextIndicatorCode(QualityStandard $standard): string
    {
        $sequence = $standard->indicators()->count() + 1;

        return 'RTM-IND-'.str_pad((string) $this->id, 4, '0', STR_PAD_LEFT).'-'.str_pad((string) $sequence, 2, '0', STR_PAD_LEFT);
    }
}
