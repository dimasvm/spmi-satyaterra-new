<?php

namespace App\Filament\Pages;

use App\Enums\ManagementReviewAttendanceStatus;
use App\Enums\ManagementReviewItemPriority;
use App\Enums\ManagementReviewItemStatus;
use App\Enums\ManagementReviewItemType;
use App\Enums\ManagementReviewStatus;
use App\Enums\StandardImprovementProposalStatus;
use App\Enums\StandardImprovementProposalType;
use App\Models\ManagementReview;
use App\Models\QualityStandard;
use App\Models\SpmiPeriod;
use App\Models\StandardIndicator;
use App\Models\Unit;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class ViewManagementReview extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartLine;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'management-reviews/{managementReview}';

    protected static ?string $title = 'Detail RTM';

    protected string $view = 'filament.pages.view-management-review';

    public ManagementReview $record;

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    public function mount(int|string $managementReview): void
    {
        $user = auth()->user();

        $this->record = ManagementReview::query()
            ->with([
                'spmiPeriod',
                'amiPeriod',
                'participants.user',
                'participants.unit',
                'items',
                'improvementProposals.qualityStandard',
                'improvementProposals.standardIndicator',
            ])
            ->when($user !== null, fn (Builder $query): Builder => $query->forUser($user))
            ->findOrFail($managementReview);
    }

    /**
     * @return array<string, int>
     */
    public function stats(): array
    {
        return [
            'items' => $this->record->items->count(),
            'major' => $this->record->items
                ->where('item_type', ManagementReviewItemType::AuditFinding)
                ->where('priority', ManagementReviewItemPriority::High)
                ->count(),
            'minor' => $this->record->items
                ->where('item_type', ManagementReviewItemType::AuditFinding)
                ->where('priority', ManagementReviewItemPriority::Medium)
                ->count(),
            'open_actions' => $this->record->items
                ->where('item_type', ManagementReviewItemType::CorrectiveAction)
                ->where('status', '!=', ManagementReviewItemStatus::FollowedUp)
                ->count(),
            'proposals' => $this->record->improvementProposals->count(),
        ];
    }

    public function markItemDiscussed(int $itemId): void
    {
        abort_unless($this->canManage(), 403);

        $this->record->items()
            ->whereKey($itemId)
            ->update(['status' => ManagementReviewItemStatus::Discussed]);

        Notification::make()
            ->success()
            ->title('Item ditandai sudah dibahas.')
            ->send();

        $this->refreshRecord();
    }

    public function deleteParticipant(int $participantId): void
    {
        abort_unless($this->canManage(), 403);

        $this->record->participants()->whereKey($participantId)->delete();

        Notification::make()
            ->success()
            ->title('Peserta dihapus.')
            ->send();

        $this->refreshRecord();
    }

    public function createProposalFromItem(int $itemId): void
    {
        abort_unless($this->canManage(), 403);

        $item = $this->record->items()->findOrFail($itemId);

        $proposal = $this->record->improvementProposals()->create([
            'proposal_type' => StandardImprovementProposalType::ReviseDocument,
            'title' => 'Usulan dari '.$item->title,
            'background' => $item->description,
            'current_condition' => $item->analysis,
            'proposed_change' => $item->recommendation ?: $item->decision ?: 'Perlu dirumuskan dari hasil pembahasan RTM.',
            'reason' => $item->decision,
            'target_spmi_period_id' => $this->record->spmi_period_id,
            'status' => StandardImprovementProposalStatus::Draft,
            'proposed_by' => auth()->id(),
        ]);

        $item->forceFill(['status' => ManagementReviewItemStatus::FollowedUp])->save();

        Notification::make()
            ->success()
            ->title('Draf usulan dibuat dari item RTM.')
            ->send();

        $this->redirect(ViewStandardImprovementProposal::getUrl(['proposal' => $proposal]), navigate: true);
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
                ->url(ManagementReviews::getUrl()),
            Action::make('editInfo')
                ->label('Edit Info')
                ->icon(Heroicon::PencilSquare)
                ->visible(fn (): bool => $this->canManage())
                ->fillForm(fn (): array => $this->record->only(['title', 'meeting_date', 'location', 'agenda', 'status']))
                ->schema([
                    TextInput::make('title')
                        ->label('Judul RTM')
                        ->required()
                        ->maxLength(255),
                    Grid::make(2)
                        ->schema([
                            DatePicker::make('meeting_date')
                                ->label('Tanggal Rapat'),
                            TextInput::make('location')
                                ->label('Lokasi')
                                ->maxLength(255),
                            Select::make('status')
                                ->label('Status')
                                ->options(ManagementReviewStatus::class)
                                ->required(),
                        ]),
                    Textarea::make('agenda')
                        ->label('Agenda')
                        ->rows(4),
                ])
                ->action(function (array $data): void {
                    $this->record->update($data);
                    $this->refreshRecord();

                    Notification::make()->success()->title('Informasi RTM diperbarui.')->send();
                }),
            Action::make('addParticipant')
                ->label('Tambah Peserta')
                ->icon(Heroicon::UserPlus)
                ->visible(fn (): bool => $this->canManage())
                ->schema([
                    Select::make('user_id')
                        ->label('User Aplikasi')
                        ->options(fn (): array => User::query()->orderBy('name')->pluck('name', 'id')->all())
                        ->searchable()
                        ->preload()
                        ->live(),
                    TextInput::make('name')
                        ->label('Nama Manual')
                        ->maxLength(255),
                    Grid::make(3)
                        ->schema([
                            TextInput::make('position')
                                ->label('Jabatan')
                                ->maxLength(255),
                            Select::make('unit_id')
                                ->label('Unit')
                                ->options(fn (): array => Unit::query()->orderBy('name')->pluck('name', 'id')->all())
                                ->searchable()
                                ->preload(),
                            Select::make('attendance_status')
                                ->label('Kehadiran')
                                ->options(ManagementReviewAttendanceStatus::class)
                                ->default(ManagementReviewAttendanceStatus::Present->value)
                                ->required(),
                        ]),
                    Textarea::make('notes')
                        ->label('Catatan')
                        ->rows(2),
                ])
                ->action(function (array $data): void {
                    $this->record->participants()->create($data);
                    $this->refreshRecord();

                    Notification::make()->success()->title('Peserta ditambahkan.')->send();
                }),
            Action::make('addItem')
                ->label('Tambah Item')
                ->icon(Heroicon::Plus)
                ->visible(fn (): bool => $this->canManage())
                ->schema([
                    Grid::make(3)
                        ->schema([
                            Select::make('item_type')
                                ->label('Jenis')
                                ->options(ManagementReviewItemType::class)
                                ->default(ManagementReviewItemType::General->value)
                                ->required(),
                            Select::make('priority')
                                ->label('Prioritas')
                                ->options(ManagementReviewItemPriority::class)
                                ->default(ManagementReviewItemPriority::Medium->value)
                                ->required(),
                            Select::make('status')
                                ->label('Status')
                                ->options(ManagementReviewItemStatus::class)
                                ->default(ManagementReviewItemStatus::Open->value)
                                ->required(),
                        ]),
                    TextInput::make('title')
                        ->label('Judul Item')
                        ->required()
                        ->maxLength(255),
                    Textarea::make('description')
                        ->label('Deskripsi')
                        ->rows(3),
                    Textarea::make('analysis')
                        ->label('Analisis')
                        ->rows(3),
                    Textarea::make('decision')
                        ->label('Keputusan')
                        ->rows(3),
                    Textarea::make('recommendation')
                        ->label('Rekomendasi')
                        ->rows(3),
                ])
                ->action(function (array $data): void {
                    $this->record->items()->create($data);
                    $this->refreshRecord();

                    Notification::make()->success()->title('Item pembahasan ditambahkan.')->send();
                }),
            Action::make('saveDecision')
                ->label('Simpan Keputusan')
                ->icon(Heroicon::OutlinedDocumentCheck)
                ->visible(fn (): bool => $this->canManage())
                ->fillForm(fn (): array => $this->record->only(['summary', 'conclusion']))
                ->schema([
                    Textarea::make('summary')
                        ->label('Ringkasan')
                        ->rows(5),
                    Textarea::make('conclusion')
                        ->label('Kesimpulan')
                        ->rows(5),
                ])
                ->action(function (array $data): void {
                    $this->record->update($data);
                    $this->refreshRecord();

                    Notification::make()->success()->title('Keputusan RTM disimpan.')->send();
                }),
            Action::make('createProposal')
                ->label('Buat Usulan Peningkatan')
                ->icon(Heroicon::OutlinedArrowTrendingUp)
                ->visible(fn (): bool => $this->canManage())
                ->schema($this->proposalSchema())
                ->action(function (array $data): void {
                    $proposal = $this->record->improvementProposals()->create([
                        ...$data,
                        'status' => StandardImprovementProposalStatus::Draft,
                        'proposed_by' => auth()->id(),
                    ]);

                    Notification::make()->success()->title('Draf usulan dibuat.')->send();

                    $this->redirect(ViewStandardImprovementProposal::getUrl(['proposal' => $proposal]), navigate: true);
                }),
            Action::make('finalize')
                ->label('Finalisasi RTM')
                ->icon(Heroicon::OutlinedLockClosed)
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->canManage() && $this->record->status !== ManagementReviewStatus::Closed)
                ->action(function (): void {
                    $this->record->forceFill([
                        'status' => ManagementReviewStatus::Closed,
                        'finalized_by' => auth()->id(),
                        'finalized_at' => now(),
                    ])->save();

                    $this->refreshRecord();

                    Notification::make()->success()->title('RTM difinalisasi.')->send();
                }),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    private function proposalSchema(): array
    {
        return [
            Grid::make(2)
                ->schema([
                    Select::make('proposal_type')
                        ->label('Jenis Usulan')
                        ->options(StandardImprovementProposalType::class)
                        ->default(StandardImprovementProposalType::ReviseDocument->value)
                        ->live()
                        ->required(),
                    Select::make('target_spmi_period_id')
                        ->label('Periode Tujuan')
                        ->options(fn (): array => SpmiPeriod::query()->orderByDesc('start_date')->pluck('name', 'id')->all())
                        ->default($this->record->spmi_period_id)
                        ->searchable()
                        ->preload(),
                    Select::make('quality_standard_id')
                        ->label('Standar Terkait')
                        ->options(fn (): array => QualityStandard::query()->orderBy('name')->pluck('name', 'id')->all())
                        ->searchable()
                        ->preload()
                        ->live(),
                    Select::make('standard_indicator_id')
                        ->label('Indikator Terkait')
                        ->options(fn (Get $get): array => StandardIndicator::query()
                            ->when($get('quality_standard_id'), fn (Builder $query, mixed $standardId): Builder => $query->where('quality_standard_id', $standardId))
                            ->orderBy('code')
                            ->pluck('statement', 'id')
                            ->all())
                        ->searchable()
                        ->preload(),
                ]),
            TextInput::make('title')
                ->label('Judul Usulan')
                ->required()
                ->maxLength(255),
            Textarea::make('proposed_change')
                ->label('Usulan Perubahan')
                ->required()
                ->rows(4),
            Textarea::make('background')
                ->label('Latar Belakang')
                ->rows(3),
            Textarea::make('expected_impact')
                ->label('Dampak yang Diharapkan')
                ->rows(3),
        ];
    }

    private function canManage(): bool
    {
        return (bool) auth()->user()?->hasAnyRole(['super_admin', 'admin_lpm']);
    }

    private function refreshRecord(): void
    {
        $this->record->refresh()->load([
            'spmiPeriod',
            'amiPeriod',
            'participants.user',
            'participants.unit',
            'items',
            'improvementProposals.qualityStandard',
            'improvementProposals.standardIndicator',
        ]);
    }
}
