<?php

namespace App\Filament\Pages;

use App\Enums\AmiAuditorRole;
use App\Enums\AmiAuditStatus;
use App\Enums\AmiFindingStatus;
use App\Filament\Resources\CorrectiveActions\CorrectiveActionResource;
use App\Models\AmiAudit;
use App\Models\User;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Livewire\WithPagination;
use UnitEnum;

class AuditSaya extends Page
{
    use WithPagination;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'Evaluasi AMI';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Audit Saya';

    protected static ?string $title = 'Audit Saya';

    protected string $view = 'filament.pages.audit-saya';

    public string $search = '';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return (bool) ($user?->isAuditor() || $user?->isAdminLpm() || $user?->isSuperAdmin());
    }

    /**
     * @return Paginator<int, AmiAudit>
     */
    public function audits(): Paginator
    {
        return $this->baseAuditQuery()
            ->when(filled($this->search), fn (Builder $query): Builder => $query->where(function (Builder $query): void {
                $query
                    ->whereHas('amiPeriod', fn (Builder $periodQuery): Builder => $periodQuery->where('name', 'like', '%'.$this->search.'%'))
                    ->orWhereHas('auditeeUnit', fn (Builder $unitQuery): Builder => $unitQuery->where('name', 'like', '%'.$this->search.'%'));
            }))
            ->orderByDesc('id')
            ->simplePaginate(10);
    }

    private function baseAuditQuery(): Builder
    {
        $user = auth()->user();

        return AmiAudit::query()
            ->with([
                'amiPeriod.spmiPeriod',
                'auditeeUnit',
                'auditorAssignments.user',
            ])
            ->withCount(['checklists', 'findings'])
            ->when($user instanceof User, fn (Builder $query): Builder => $query->forUser($user), fn (Builder $query): Builder => $query->whereRaw('1 = 0'));
    }

    /**
     * @return array<string, int>
     */
    public function summary(): array
    {
        $audits = $this->baseAuditQuery()->get();

        return [
            'total' => $audits->count(),
            'active' => $audits->whereNotIn('status', [
                AmiAuditStatus::Completed,
                AmiAuditStatus::Finalized,
            ])->count(),
            'unfinished_checklists' => $audits->sum(fn (AmiAudit $audit): int => $audit->checklists()
                ->whereNull('assessment_result')
                ->count()),
            'open_findings' => $audits->sum(fn (AmiAudit $audit): int => $audit->findings()->whereNot('status', AmiFindingStatus::Closed->value)->count()),
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * @return array<string, int>
     */
    public function checklistProgress(AmiAudit $audit): array
    {
        $total = (int) ($audit->checklists_count ?? $audit->checklists()->count());
        $completed = $audit->checklists()
            ->whereNotNull('assessment_result')
            ->count();

        return [
            'total' => $total,
            'completed' => $completed,
            'percent' => $total > 0 ? (int) round(($completed / $total) * 100) : 0,
        ];
    }

    public function auditorRoleLabel(AmiAudit $audit): string
    {
        $role = $this->auditorRole($audit);

        return $role?->getLabel() ?? (auth()->user()?->isAuditor() ? '-' : 'Monitoring');
    }

    public function auditorRoleColor(AmiAudit $audit): string|array|null
    {
        return $this->auditorRole($audit)?->getColor() ?? 'gray';
    }

    public function workspaceUrl(AmiAudit $audit): string
    {
        return AuditWorkspace::getUrl(['audit' => $audit->id]);
    }

    public function correctiveActionUrl(): string
    {
        return CorrectiveActionResource::getUrl('index');
    }

    private function auditorRole(AmiAudit $audit): ?AmiAuditorRole
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return null;
        }

        return $audit->auditorAssignments
            ->first(fn ($assignment): bool => (int) $assignment->user_id === (int) $user->id)
            ?->role;
    }
}
