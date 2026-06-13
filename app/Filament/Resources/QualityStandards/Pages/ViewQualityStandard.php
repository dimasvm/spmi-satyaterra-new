<?php

namespace App\Filament\Resources\QualityStandards\Pages;

use App\Enums\IndicatorAssignmentStatus;
use App\Enums\QualityDocumentStatus;
use App\Enums\QualityStandardStatus;
use App\Filament\Pages\StandardImprovementProposals;
use App\Filament\Resources\IndicatorAchievements\IndicatorAchievementResource;
use App\Filament\Resources\IndicatorUnitAssignments\IndicatorUnitAssignmentResource;
use App\Filament\Resources\QualityDocuments\QualityDocumentResource;
use App\Filament\Resources\QualityStandards\QualityStandardResource;
use App\Filament\Resources\StandardIndicators\StandardIndicatorResource;
use App\Models\IndicatorAchievement;
use App\Models\IndicatorUnitAssignment;
use App\Models\QualityDocument;
use App\Models\QualityStandard;
use App\Models\StandardIndicator;
use App\Models\StandardRevisionHistory;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;

class ViewQualityStandard extends ViewRecord
{
    protected static string $resource = QualityStandardResource::class;

    protected string $view = 'filament.resources.quality-standards.pages.view-quality-standard';

    public string $activeTab = 'summary';

    /**
     * @return array<int, array{key: string, label: string, count: int|null}>
     */
    public function tabs(): array
    {
        return [
            ['key' => 'summary', 'label' => 'Ringkasan', 'count' => null],
            ['key' => 'indicators', 'label' => 'Indikator', 'count' => $this->indicators()->count()],
            ['key' => 'documents', 'label' => 'Dokumen Terkait', 'count' => $this->documents()->count()],
            ['key' => 'assignments', 'label' => 'Unit Ditugaskan', 'count' => $this->assignments()->count()],
            ['key' => 'achievements', 'label' => 'Capaian', 'count' => $this->achievements()->count()],
            ['key' => 'revisions', 'label' => 'Riwayat Revisi', 'count' => $this->revisionItems()->count()],
        ];
    }

    public function record(): QualityStandard
    {
        /** @var QualityStandard $record */
        $record = $this->getRecord()->loadMissing([
            'category',
            'spmiPeriod',
            'approver',
        ]);

        return $record;
    }

    /**
     * @return array<string, int>
     */
    public function stats(): array
    {
        return [
            'indicators' => $this->indicators()->count(),
            'documents' => $this->documents()->count(),
            'assignments' => $this->assignments()->count(),
            'achievements' => $this->achievements()->count(),
        ];
    }

    /**
     * @return Collection<int, StandardIndicator>
     */
    public function indicators(): Collection
    {
        return $this->record()
            ->indicators()
            ->withCount('assignments')
            ->orderBy('code')
            ->get();
    }

    /**
     * @return Collection<int, QualityDocument>
     */
    public function documents(): Collection
    {
        $user = auth()->user();

        return $this->record()
            ->documents()
            ->with(['uploadedBy', 'approvedBy'])
            ->when($user instanceof User, fn (Builder $query): Builder => $query->visibleToUser($user))
            ->latest()
            ->get();
    }

    /**
     * @return Collection<int, IndicatorUnitAssignment>
     */
    public function assignments(): Collection
    {
        $user = auth()->user();

        return $this->record()
            ->assignments()
            ->with(['unit', 'standardIndicator', 'spmiPeriod'])
            ->withCount('achievements')
            ->when($user instanceof User, fn (Builder $query): Builder => $query->forUser($user))
            ->latest('due_date')
            ->get();
    }

    /**
     * @return Collection<int, IndicatorAchievement>
     */
    public function achievements(): Collection
    {
        $user = auth()->user();

        return $this->record()
            ->achievements()
            ->with([
                'assignment.unit',
                'assignment.standardIndicator',
                'latestReview',
            ])
            ->withCount('evidences')
            ->when($user instanceof User, fn (Builder $query): Builder => $query->forUser($user))
            ->latest()
            ->get();
    }

    /**
     * @return Collection<int, StandardRevisionHistory>
     */
    public function revisionHistories(): Collection
    {
        return $this->record()
            ->revisionHistories()
            ->with(['standardIndicator', 'standardImprovementProposal', 'revisedBy'])
            ->latest('revised_at')
            ->get();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function revisionItems(): Collection
    {
        $histories = $this->revisionHistories()
            ->map(fn (StandardRevisionHistory $history): array => [
                'type' => $history->revision_type?->getLabel() ?? 'Riwayat revisi',
                'title' => $history->standardImprovementProposal?->title
                    ?? $history->standardIndicator?->code
                    ?? $this->record()->code,
                'description' => $history->notes ?: 'Perubahan standar tercatat dalam riwayat revisi.',
                'status' => 'Tercatat',
                'date' => $history->revised_at?->format('d M Y H:i') ?? '-',
                'actor' => $history->revisedBy?->name ?? '-',
            ]);

        $proposals = $this->record()
            ->improvementProposals()
            ->with(['standardIndicator', 'proposedBy'])
            ->latest()
            ->get()
            ->map(fn ($proposal): array => [
                'type' => $proposal->proposal_type?->getLabel() ?? 'Usulan peningkatan',
                'title' => $proposal->title,
                'description' => $proposal->proposed_change ?: $proposal->reason ?: 'Usulan peningkatan terkait standar ini.',
                'status' => $proposal->status?->getLabel() ?? '-',
                'date' => $proposal->created_at?->format('d M Y H:i') ?? '-',
                'actor' => $proposal->proposedBy?->name ?? '-',
            ]);

        return $histories->concat($proposals)->values();
    }

    public function targetLabel(StandardIndicator $indicator): string
    {
        return trim(implode(' ', array_filter([
            $indicator->target_operator?->value,
            $indicator->target_value,
            $indicator->target_unit,
        ]))) ?: '-';
    }

    public function documentUrl(QualityDocument $document): ?string
    {
        if (filled($document->external_url)) {
            return $document->external_url;
        }

        if (blank($document->file_path)) {
            return null;
        }

        return URL::signedRoute('quality-documents.file', ['document' => $document], absolute: false);
    }

    public function indicatorEditUrl(StandardIndicator $indicator): string
    {
        return StandardIndicatorResource::getUrl('edit', ['record' => $indicator]);
    }

    public function documentCreateUrl(): string
    {
        return QualityDocumentResource::getUrl('create').'?quality_standard_id='.$this->record()->getKey();
    }

    public function documentEditUrl(QualityDocument $document): string
    {
        return QualityDocumentResource::getUrl('edit', ['record' => $document]);
    }

    public function approveDocument(int $documentId): void
    {
        $document = $this->record()->documents()->findOrFail($documentId);

        abort_unless(auth()->user()?->can('approve', $document), 403);

        if (blank($document->file_path) && blank($document->external_url)) {
            Notification::make()
                ->danger()
                ->title('Dokumen belum memiliki file atau tautan.')
                ->send();

            return;
        }

        $document->update([
            'status' => QualityDocumentStatus::Active,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        Notification::make()
            ->success()
            ->title('Dokumen mutu disetujui dan diaktifkan.')
            ->send();
    }

    public function archiveDocument(int $documentId): void
    {
        $document = $this->record()->documents()->findOrFail($documentId);

        abort_unless(auth()->user()?->can('archive', $document), 403);

        $document->update([
            'status' => QualityDocumentStatus::Archived,
        ]);

        Notification::make()
            ->warning()
            ->title('Dokumen mutu diarsipkan.')
            ->send();
    }

    public function assignmentEditUrl(IndicatorUnitAssignment $assignment): string
    {
        return IndicatorUnitAssignmentResource::getUrl('edit', ['record' => $assignment]);
    }

    public function achievementViewUrl(IndicatorAchievement $achievement): string
    {
        return IndicatorAchievementResource::getUrl('view', ['record' => $achievement]);
    }

    public function proposalWorkspaceUrl(): string
    {
        return StandardImprovementProposals::getUrl();
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Edit Standar')
                ->icon(Heroicon::OutlinedPencilSquare),
            Action::make('submitApproval')
                ->label('Ajukan Approval')
                ->icon(Heroicon::OutlinedPaperAirplane)
                ->color('info')
                ->visible(fn (): bool => (auth()->user()?->can('update', $this->record()) ?? false)
                    && in_array($this->record()->status, [
                        QualityStandardStatus::Draft,
                        QualityStandardStatus::Revised,
                    ], true))
                ->requiresConfirmation()
                ->action(function (): void {
                    $this->record()->update([
                        'status' => QualityStandardStatus::Submitted,
                    ]);

                    Notification::make()
                        ->success()
                        ->title('Standar diajukan untuk approval.')
                        ->send();
                }),
            Action::make('approve')
                ->label('Approve')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('success')
                ->visible(fn (): bool => (auth()->user()?->can('update', $this->record()) ?? false)
                    && $this->record()->status === QualityStandardStatus::Submitted)
                ->requiresConfirmation()
                ->action(function (): void {
                    $this->record()->update([
                        'status' => QualityStandardStatus::Active,
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                    ]);

                    Notification::make()
                        ->success()
                        ->title('Standar disetujui dan diaktifkan.')
                        ->send();
                }),
            Action::make('archive')
                ->label('Arsipkan')
                ->icon(Heroicon::OutlinedArchiveBox)
                ->color('warning')
                ->visible(fn (): bool => (auth()->user()?->can('update', $this->record()) ?? false)
                    && $this->record()->status !== QualityStandardStatus::Archived)
                ->requiresConfirmation()
                ->action(function (): void {
                    $this->record()->update([
                        'status' => QualityStandardStatus::Archived,
                    ]);

                    Notification::make()
                        ->warning()
                        ->title('Standar diarsipkan.')
                        ->send();
                }),
            Action::make('createRevisionProposal')
                ->label('Buat Usulan Revisi')
                ->icon(Heroicon::OutlinedArrowTrendingUp)
                ->color('gray')
                ->url(fn (): string => $this->proposalWorkspaceUrl()),
        ];
    }

    public function assignmentStatusColor(IndicatorUnitAssignment $assignment): string
    {
        return match ($assignment->status) {
            IndicatorAssignmentStatus::Validated => 'success',
            IndicatorAssignmentStatus::Submitted, IndicatorAssignmentStatus::InProgress => 'info',
            IndicatorAssignmentStatus::Returned => 'warning',
            default => 'gray',
        };
    }

    public function documentStatusColor(QualityDocument $document): string
    {
        return match ($document->status) {
            QualityDocumentStatus::Active, QualityDocumentStatus::Approved => 'success',
            QualityDocumentStatus::Submitted => 'info',
            QualityDocumentStatus::Archived => 'gray',
            default => 'warning',
        };
    }
}
