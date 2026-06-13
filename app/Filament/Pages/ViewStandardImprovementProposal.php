<?php

namespace App\Filament\Pages;

use App\Enums\StandardImprovementProposalStatus;
use App\Models\StandardImprovementProposal;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Throwable;

class ViewStandardImprovementProposal extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowTrendingUp;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'standard-improvement-proposals/{proposal}';

    protected static ?string $title = 'Detail Usulan Peningkatan';

    protected string $view = 'filament.pages.view-standard-improvement-proposal';

    public StandardImprovementProposal $record;

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    public function mount(int|string $proposal): void
    {
        $user = auth()->user();

        $this->record = StandardImprovementProposal::query()
            ->with([
                'managementReview',
                'qualityStandard',
                'standardIndicator',
                'targetSpmiPeriod',
                'proposedBy',
                'reviewedBy',
                'implementedBy',
                'createdStandard',
                'createdIndicator',
                'revisionHistories.revisedBy',
            ])
            ->when($user !== null, fn (Builder $query): Builder => $query->forUser($user))
            ->findOrFail($proposal);
    }

    /**
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Kembali')
                ->icon(Heroicon::ArrowLeft)
                ->color('gray')
                ->url(StandardImprovementProposals::getUrl()),
            Action::make('submit')
                ->label('Ajukan')
                ->icon(Heroicon::OutlinedPaperAirplane)
                ->visible(fn (): bool => $this->canCreateOrImplement() && $this->record->status === StandardImprovementProposalStatus::Draft)
                ->requiresConfirmation()
                ->action(function (): void {
                    $this->record->submit(auth()->user());
                    $this->refreshRecord();

                    Notification::make()->success()->title('Usulan diajukan.')->send();
                }),
            Action::make('approve')
                ->label('Setujui')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('success')
                ->visible(fn (): bool => $this->canReview() && $this->record->status === StandardImprovementProposalStatus::Submitted)
                ->schema([
                    Textarea::make('review_notes')
                        ->label('Catatan Review')
                        ->rows(3),
                ])
                ->action(function (array $data): void {
                    $this->record->approve(auth()->user(), $data['review_notes'] ?? null);
                    $this->refreshRecord();

                    Notification::make()->success()->title('Usulan disetujui.')->send();
                }),
            Action::make('reject')
                ->label('Tolak')
                ->icon(Heroicon::OutlinedXCircle)
                ->color('danger')
                ->visible(fn (): bool => $this->canReview() && $this->record->status === StandardImprovementProposalStatus::Submitted)
                ->schema([
                    Textarea::make('review_notes')
                        ->label('Alasan Penolakan')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data): void {
                    $this->record->reject(auth()->user(), $data['review_notes']);
                    $this->refreshRecord();

                    Notification::make()->success()->title('Usulan ditolak.')->send();
                }),
            Action::make('implement')
                ->label('Implementasikan')
                ->icon(Heroicon::OutlinedRocketLaunch)
                ->color('primary')
                ->visible(fn (): bool => $this->canCreateOrImplement() && $this->record->status === StandardImprovementProposalStatus::Approved)
                ->requiresConfirmation()
                ->action(function (): void {
                    try {
                        $this->record->implement(auth()->user());
                        $this->refreshRecord();

                        Notification::make()->success()->title('Usulan diimplementasikan sebagai draft/revisi.')->send();
                    } catch (Throwable $exception) {
                        Notification::make()
                            ->danger()
                            ->title('Implementasi gagal.')
                            ->body($exception->getMessage())
                            ->send();
                    }
                }),
        ];
    }

    private function canCreateOrImplement(): bool
    {
        return (bool) auth()->user()?->hasAnyRole(['super_admin', 'admin_lpm']);
    }

    private function canReview(): bool
    {
        return (bool) auth()->user()?->hasAnyRole(['super_admin', 'pimpinan']);
    }

    private function refreshRecord(): void
    {
        $this->record->refresh()->load([
            'managementReview',
            'qualityStandard',
            'standardIndicator',
            'targetSpmiPeriod',
            'proposedBy',
            'reviewedBy',
            'implementedBy',
            'createdStandard',
            'createdIndicator',
            'revisionHistories.revisedBy',
        ]);
    }
}
