<?php

namespace App\Filament\Pages;

use App\Enums\AchievementStatus;
use App\Enums\CorrectiveActionStatus;
use App\Enums\ManagementReviewItemPriority;
use App\Enums\ManagementReviewItemStatus;
use App\Enums\ManagementReviewItemType;
use App\Enums\ManagementReviewStatus;
use App\Enums\StandardImprovementProposalStatus;
use App\Models\AmiFinding;
use App\Models\AmiPeriod;
use App\Models\CorrectiveAction;
use App\Models\IndicatorAchievement;
use App\Models\ManagementReview;
use App\Models\ManagementReviewItem;
use App\Models\SpmiPeriod;
use App\Models\StandardImprovementProposal;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ManagementReviews extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartLine;

    protected static string|UnitEnum|null $navigationGroup = 'Peningkatan';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Rapat Tinjauan Manajemen';

    protected static ?string $title = 'Rapat Tinjauan Manajemen';

    protected string $view = 'filament.pages.management-reviews';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return (bool) ($user?->isSuperAdmin() || $user?->isAdminLpm() || $user?->isPimpinan());
    }

    public function table(Table $table): Table
    {
        $user = auth()->user();

        return $table
            ->query(ManagementReview::query()
                ->with(['spmiPeriod', 'amiPeriod'])
                ->withCount(['items', 'improvementProposals'])
                ->when($user !== null, fn (Builder $query): Builder => $query->forUser($user)))
            ->columns([
                TextColumn::make('title')
                    ->label('Judul RTM')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('spmiPeriod.name')
                    ->label('Periode SPMI')
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('amiPeriod.name')
                    ->label('Periode AMI')
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('meeting_date')
                    ->label('Tanggal Rapat')
                    ->date('d M Y')
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('items_count')
                    ->label('Item')
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('improvement_proposals_count')
                    ->label('Usulan')
                    ->alignCenter()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('spmi_period_id')
                    ->label('Periode SPMI')
                    ->relationship('spmiPeriod', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('ami_period_id')
                    ->label('Periode AMI')
                    ->relationship('amiPeriod', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(ManagementReviewStatus::class),
            ])
            ->recordActions([
                Action::make('open')
                    ->label('Buka')
                    ->icon(Heroicon::OutlinedArrowRight)
                    ->url(fn (ManagementReview $record): string => ViewManagementReview::getUrl(['managementReview' => $record])),
            ])
            ->defaultSort('meeting_date', 'desc');
    }

    /**
     * @return array<string, int>
     */
    public function stats(): array
    {
        $query = ManagementReview::query()
            ->when(auth()->user(), fn (Builder $query, $user): Builder => $query->forUser($user));

        return [
            'total' => (clone $query)->count(),
            'draft' => (clone $query)->where('status', ManagementReviewStatus::Draft)->count(),
            'completed' => (clone $query)->whereIn('status', [ManagementReviewStatus::Completed, ManagementReviewStatus::Closed])->count(),
            'pending_proposals' => StandardImprovementProposal::query()
                ->when(auth()->user(), fn (Builder $query, $user): Builder => $query->forUser($user))
                ->where('status', StandardImprovementProposalStatus::Submitted)
                ->count(),
        ];
    }

    /**
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('createRtm')
                ->label('Buat RTM')
                ->icon(Heroicon::Plus)
                ->visible(fn (): bool => (bool) auth()->user()?->hasAnyRole(['super_admin', 'admin_lpm']))
                ->steps([
                    Step::make('Informasi RTM')
                        ->schema([
                            TextInput::make('title')
                                ->label('Judul RTM')
                                ->required()
                                ->maxLength(255),
                            Grid::make(2)
                                ->schema([
                                    Select::make('spmi_period_id')
                                        ->label('Periode SPMI')
                                        ->options(fn (): array => SpmiPeriod::query()->orderByDesc('start_date')->pluck('name', 'id')->all())
                                        ->searchable()
                                        ->preload()
                                        ->live()
                                        ->required(),
                                    Select::make('ami_period_id')
                                        ->label('Periode AMI')
                                        ->options(fn (): array => AmiPeriod::query()->orderByDesc('start_date')->pluck('name', 'id')->all())
                                        ->searchable()
                                        ->preload()
                                        ->live(),
                                    DatePicker::make('meeting_date')
                                        ->label('Tanggal Rapat'),
                                    TextInput::make('location')
                                        ->label('Lokasi')
                                        ->maxLength(255),
                                ]),
                            Textarea::make('agenda')
                                ->label('Agenda')
                                ->rows(4),
                        ]),
                    Step::make('Pilih Bahan RTM')
                        ->schema([
                            CheckboxList::make('ami_finding_ids')
                                ->label('Temuan Audit')
                                ->options(fn (Get $get): array => $this->amiFindingOptions($get('ami_period_id')))
                                ->searchable()
                                ->bulkToggleable()
                                ->columns(1),
                            CheckboxList::make('corrective_action_ids')
                                ->label('Tindak Lanjut Belum Diterima')
                                ->options(fn (Get $get): array => $this->correctiveActionOptions($get('ami_period_id')))
                                ->searchable()
                                ->bulkToggleable()
                                ->columns(1),
                            CheckboxList::make('indicator_achievement_ids')
                                ->label('Capaian Belum Tercapai / Dikembalikan')
                                ->options(fn (Get $get): array => $this->indicatorAchievementOptions($get('spmi_period_id')))
                                ->searchable()
                                ->bulkToggleable()
                                ->columns(1),
                        ]),
                    Step::make('Review')
                        ->schema([
                            Section::make('Ringkasan')
                                ->description('RTM akan disimpan sebagai draf. Item yang dipilih masuk sebagai bahan pembahasan.')
                                ->schema([
                                    Grid::make(3)
                                        ->schema([
                                            Placeholder::make('selected_findings_count')
                                                ->label('Temuan Audit')
                                                ->content(fn (Get $get): int => count($get('ami_finding_ids') ?? [])),
                                            Placeholder::make('selected_actions_count')
                                                ->label('Tindak Lanjut')
                                                ->content(fn (Get $get): int => count($get('corrective_action_ids') ?? [])),
                                            Placeholder::make('selected_achievements_count')
                                                ->label('Capaian')
                                                ->content(fn (Get $get): int => count($get('indicator_achievement_ids') ?? [])),
                                        ]),
                                ]),
                        ]),
                    Step::make('Simpan Draf')
                        ->schema([
                            Section::make('Konfirmasi Draf RTM')
                                ->description('Simpan draft RTM untuk mulai mengelola peserta, item pembahasan, keputusan, dan usulan peningkatan.')
                                ->schema([
                                    Placeholder::make('draft_title')
                                        ->label('Judul')
                                        ->content(fn (Get $get): string => (string) ($get('title') ?: '-')),
                                    Placeholder::make('draft_materials')
                                        ->label('Bahan RTM')
                                        ->content(fn (Get $get): string => sprintf(
                                            '%d temuan, %d tindak lanjut, %d capaian',
                                            count($get('ami_finding_ids') ?? []),
                                            count($get('corrective_action_ids') ?? []),
                                            count($get('indicator_achievement_ids') ?? []),
                                        )),
                                ]),
                        ]),
                ])
                ->modalSubmitActionLabel('Simpan Draf RTM')
                ->action(fn (array $data): null => $this->createManagementReview($data)),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function createManagementReview(array $data): null
    {
        $review = ManagementReview::query()->create([
            'spmi_period_id' => $data['spmi_period_id'] ?? null,
            'ami_period_id' => $data['ami_period_id'] ?? null,
            'title' => $data['title'],
            'meeting_date' => $data['meeting_date'] ?? null,
            'location' => $data['location'] ?? null,
            'agenda' => $data['agenda'] ?? null,
            'status' => ManagementReviewStatus::Draft,
            'created_by' => auth()->id(),
        ]);

        $this->attachSelectedMaterials($review, $data);

        Notification::make()
            ->success()
            ->title('Draf RTM berhasil dibuat.')
            ->send();

        $this->redirect(ViewManagementReview::getUrl(['managementReview' => $review]), navigate: true);

        return null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function attachSelectedMaterials(ManagementReview $review, array $data): void
    {
        AmiFinding::query()
            ->with(['standardIndicator'])
            ->whereIn('id', $data['ami_finding_ids'] ?? [])
            ->get()
            ->each(fn (AmiFinding $finding): ManagementReviewItem => $review->items()->create([
                'item_type' => ManagementReviewItemType::AuditFinding,
                'reference_type' => AmiFinding::class,
                'reference_id' => $finding->id,
                'title' => $finding->finding_number ?: 'Temuan Audit',
                'description' => $finding->description,
                'recommendation' => $finding->recommendation,
                'priority' => $finding->category?->value === 'major' ? ManagementReviewItemPriority::High : ManagementReviewItemPriority::Medium,
                'status' => ManagementReviewItemStatus::Open,
            ]));

        CorrectiveAction::query()
            ->with(['finding'])
            ->whereIn('id', $data['corrective_action_ids'] ?? [])
            ->get()
            ->each(fn (CorrectiveAction $action): ManagementReviewItem => $review->items()->create([
                'item_type' => ManagementReviewItemType::CorrectiveAction,
                'reference_type' => CorrectiveAction::class,
                'reference_id' => $action->id,
                'title' => 'Tindak Lanjut '.$action->finding?->finding_number,
                'description' => $action->action_plan,
                'analysis' => $action->root_cause_analysis,
                'priority' => ManagementReviewItemPriority::Medium,
                'status' => ManagementReviewItemStatus::Open,
            ]));

        IndicatorAchievement::query()
            ->with(['assignment.standardIndicator', 'assignment.unit'])
            ->whereIn('id', $data['indicator_achievement_ids'] ?? [])
            ->get()
            ->each(fn (IndicatorAchievement $achievement): ManagementReviewItem => $review->items()->create([
                'item_type' => ManagementReviewItemType::IndicatorAchievement,
                'reference_type' => IndicatorAchievement::class,
                'reference_id' => $achievement->id,
                'title' => $achievement->assignment?->standardIndicator?->code.' - '.$achievement->assignment?->unit?->name,
                'description' => $achievement->notes ?: $achievement->realization_text,
                'priority' => ManagementReviewItemPriority::Medium,
                'status' => ManagementReviewItemStatus::Open,
            ]));
    }

    /**
     * @return array<int, string>
     */
    private function amiFindingOptions(mixed $amiPeriodId): array
    {
        return AmiFinding::query()
            ->with(['audit.auditeeUnit'])
            ->when($amiPeriodId, fn (Builder $query): Builder => $query->whereHas('audit', fn (Builder $auditQuery): Builder => $auditQuery->where('ami_period_id', $amiPeriodId)))
            ->latest()
            ->limit(50)
            ->get()
            ->mapWithKeys(fn (AmiFinding $finding): array => [
                $finding->id => trim(($finding->finding_number ?: 'Temuan').' - '.str($finding->description)->limit(90)),
            ])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function correctiveActionOptions(mixed $amiPeriodId): array
    {
        return CorrectiveAction::query()
            ->with(['finding.audit'])
            ->where('status', '!=', CorrectiveActionStatus::Accepted->value)
            ->when($amiPeriodId, fn (Builder $query): Builder => $query->whereHas('finding.audit', fn (Builder $auditQuery): Builder => $auditQuery->where('ami_period_id', $amiPeriodId)))
            ->latest()
            ->limit(50)
            ->get()
            ->mapWithKeys(fn (CorrectiveAction $action): array => [
                $action->id => trim(($action->finding?->finding_number ?: 'Temuan').' - '.str($action->action_plan)->limit(90)),
            ])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function indicatorAchievementOptions(mixed $spmiPeriodId): array
    {
        return IndicatorAchievement::query()
            ->with(['assignment.standardIndicator', 'assignment.unit'])
            ->where('achievement_status', AchievementStatus::NotAchieved->value)
            ->when($spmiPeriodId, fn (Builder $query): Builder => $query->whereHas('assignment', fn (Builder $assignmentQuery): Builder => $assignmentQuery->where('spmi_period_id', $spmiPeriodId)))
            ->latest()
            ->limit(50)
            ->get()
            ->mapWithKeys(fn (IndicatorAchievement $achievement): array => [
                $achievement->id => trim(($achievement->assignment?->standardIndicator?->code ?: 'Indikator').' - '.($achievement->assignment?->unit?->name ?: 'Unit')),
            ])
            ->all();
    }
}
