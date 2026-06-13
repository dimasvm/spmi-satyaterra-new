<?php

namespace App\Filament\Pages;

use App\Enums\StandardImprovementProposalStatus;
use App\Enums\StandardImprovementProposalType;
use App\Enums\TargetOperator;
use App\Models\ManagementReview;
use App\Models\QualityStandard;
use App\Models\SpmiPeriod;
use App\Models\StandardImprovementProposal;
use App\Models\StandardIndicator;
use BackedEnum;
use Filament\Actions\Action;
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

class StandardImprovementProposals extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowTrendingUp;

    protected static string|UnitEnum|null $navigationGroup = 'Peningkatan';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Usulan Peningkatan Standar';

    protected static ?string $title = 'Usulan Peningkatan Standar';

    protected string $view = 'filament.pages.standard-improvement-proposals';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return (bool) ($user?->isSuperAdmin() || $user?->isAdminLpm() || $user?->isPimpinan());
    }

    public function table(Table $table): Table
    {
        $user = auth()->user();

        return $table
            ->query(StandardImprovementProposal::query()
                ->with(['qualityStandard', 'standardIndicator', 'targetSpmiPeriod'])
                ->when($user !== null, fn (Builder $query): Builder => $query->forUser($user)))
            ->columns([
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('proposal_type')
                    ->label('Jenis Usulan')
                    ->badge()
                    ->sortable(),
                TextColumn::make('qualityStandard.name')
                    ->label('Standar')
                    ->placeholder('-')
                    ->limit(35)
                    ->searchable(),
                TextColumn::make('standardIndicator.code')
                    ->label('Indikator')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('targetSpmiPeriod.name')
                    ->label('Periode Tujuan')
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(StandardImprovementProposalStatus::class),
                SelectFilter::make('proposal_type')
                    ->label('Jenis Usulan')
                    ->options(StandardImprovementProposalType::class),
                SelectFilter::make('target_spmi_period_id')
                    ->label('Periode Tujuan')
                    ->relationship('targetSpmiPeriod', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('quality_standard_id')
                    ->label('Standar')
                    ->relationship('qualityStandard', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                Action::make('open')
                    ->label('Buka')
                    ->icon(Heroicon::OutlinedArrowRight)
                    ->url(fn (StandardImprovementProposal $record): string => ViewStandardImprovementProposal::getUrl(['proposal' => $record])),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * @return array<string, int>
     */
    public function stats(): array
    {
        $query = StandardImprovementProposal::query()
            ->when(auth()->user(), fn (Builder $query, $user): Builder => $query->forUser($user));

        return [
            'draft' => (clone $query)->where('status', StandardImprovementProposalStatus::Draft)->count(),
            'submitted' => (clone $query)->where('status', StandardImprovementProposalStatus::Submitted)->count(),
            'approved' => (clone $query)->where('status', StandardImprovementProposalStatus::Approved)->count(),
            'rejected' => (clone $query)->where('status', StandardImprovementProposalStatus::Rejected)->count(),
            'implemented' => (clone $query)->where('status', StandardImprovementProposalStatus::Implemented)->count(),
        ];
    }

    /**
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('createDraft')
                ->label('Buat Usulan')
                ->icon(Heroicon::Plus)
                ->visible(fn (): bool => $this->canCreateProposal())
                ->steps($this->proposalSteps())
                ->modalSubmitActionLabel('Simpan Draf')
                ->action(fn (array $data): null => $this->createProposal($data, StandardImprovementProposalStatus::Draft)),
            Action::make('createSubmitted')
                ->label('Ajukan Usulan')
                ->icon(Heroicon::OutlinedPaperAirplane)
                ->color('success')
                ->visible(fn (): bool => $this->canCreateProposal())
                ->steps($this->proposalSteps())
                ->modalSubmitActionLabel('Ajukan Usulan')
                ->action(fn (array $data): null => $this->createProposal($data, StandardImprovementProposalStatus::Submitted)),
        ];
    }

    /**
     * @return array<int, Step>
     */
    private function proposalSteps(): array
    {
        return [
            Step::make('Jenis Usulan')
                ->schema([
                    Select::make('management_review_id')
                        ->label('RTM Asal')
                        ->options(fn (): array => ManagementReview::query()->latest()->pluck('title', 'id')->all())
                        ->searchable()
                        ->preload(),
                    Select::make('proposal_type')
                        ->label('Jenis Usulan')
                        ->options(StandardImprovementProposalType::class)
                        ->live()
                        ->required(),
                ]),
            Step::make('Objek Terkait')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('quality_standard_id')
                                ->label('Standar Terkait')
                                ->options(fn (): array => QualityStandard::query()->orderBy('name')->pluck('name', 'id')->all())
                                ->searchable()
                                ->preload()
                                ->live()
                                ->required(fn (Get $get): bool => in_array($get('proposal_type'), [
                                    StandardImprovementProposalType::ReviseStandard->value,
                                    StandardImprovementProposalType::ReviseIndicator->value,
                                    StandardImprovementProposalType::ReviseTarget->value,
                                    StandardImprovementProposalType::CreateNewIndicator->value,
                                    StandardImprovementProposalType::RemoveIndicator->value,
                                    StandardImprovementProposalType::ReviseDocument->value,
                                ], true)),
                            Select::make('standard_indicator_id')
                                ->label('Indikator Terkait')
                                ->options(fn (Get $get): array => StandardIndicator::query()
                                    ->when($get('quality_standard_id'), fn (Builder $query, mixed $standardId): Builder => $query->where('quality_standard_id', $standardId))
                                    ->orderBy('code')
                                    ->pluck('statement', 'id')
                                    ->all())
                                ->searchable()
                                ->preload()
                                ->required(fn (Get $get): bool => in_array($get('proposal_type'), [
                                    StandardImprovementProposalType::ReviseIndicator->value,
                                    StandardImprovementProposalType::ReviseTarget->value,
                                    StandardImprovementProposalType::RemoveIndicator->value,
                                ], true)),
                        ]),
                    TextInput::make('proposed_standard_name')
                        ->label('Nama Standar Baru')
                        ->visible(fn (Get $get): bool => $get('proposal_type') === StandardImprovementProposalType::CreateNewStandard->value)
                        ->required(fn (Get $get): bool => $get('proposal_type') === StandardImprovementProposalType::CreateNewStandard->value),
                    Textarea::make('proposed_standard_description')
                        ->label('Deskripsi Standar Baru')
                        ->visible(fn (Get $get): bool => $get('proposal_type') === StandardImprovementProposalType::CreateNewStandard->value)
                        ->rows(3),
                    Textarea::make('proposed_indicator_statement')
                        ->label('Rumusan Indikator Usulan')
                        ->visible(fn (Get $get): bool => in_array($get('proposal_type'), [
                            StandardImprovementProposalType::CreateNewIndicator->value,
                            StandardImprovementProposalType::ReviseIndicator->value,
                        ], true))
                        ->required(fn (Get $get): bool => $get('proposal_type') === StandardImprovementProposalType::CreateNewIndicator->value)
                        ->rows(3),
                ]),
            Step::make('Detail Usulan')
                ->schema([
                    TextInput::make('title')
                        ->label('Judul Usulan')
                        ->required()
                        ->maxLength(255),
                    Textarea::make('background')
                        ->label('Latar Belakang')
                        ->rows(3),
                    Textarea::make('current_condition')
                        ->label('Kondisi Saat Ini')
                        ->rows(3),
                    Textarea::make('proposed_change')
                        ->label('Usulan Perubahan')
                        ->required()
                        ->rows(4),
                    Textarea::make('reason')
                        ->label('Alasan')
                        ->rows(3),
                    Textarea::make('expected_impact')
                        ->label('Dampak yang Diharapkan')
                        ->rows(3),
                    Grid::make(3)
                        ->schema([
                            Select::make('proposed_target_operator')
                                ->label('Operator Target')
                                ->options(TargetOperator::class)
                                ->visible(fn (Get $get): bool => in_array($get('proposal_type'), [
                                    StandardImprovementProposalType::ReviseTarget->value,
                                    StandardImprovementProposalType::CreateNewIndicator->value,
                                ], true)),
                            TextInput::make('proposed_target_value')
                                ->label('Nilai Target')
                                ->numeric()
                                ->visible(fn (Get $get): bool => in_array($get('proposal_type'), [
                                    StandardImprovementProposalType::ReviseTarget->value,
                                    StandardImprovementProposalType::CreateNewIndicator->value,
                                ], true)),
                            TextInput::make('proposed_target_unit')
                                ->label('Satuan Target')
                                ->visible(fn (Get $get): bool => in_array($get('proposal_type'), [
                                    StandardImprovementProposalType::ReviseTarget->value,
                                    StandardImprovementProposalType::CreateNewIndicator->value,
                                ], true)),
                        ]),
                ]),
            Step::make('Periode Tujuan')
                ->schema([
                    Select::make('target_spmi_period_id')
                        ->label('Periode Tujuan')
                        ->options(fn (): array => SpmiPeriod::query()->orderByDesc('start_date')->pluck('name', 'id')->all())
                        ->searchable()
                        ->preload(),
                ]),
            Step::make('Review')
                ->schema([
                    Section::make('Ringkasan')
                        ->description('Periksa kembali jenis, objek, dan ringkasan perubahan sebelum menyimpan.')
                        ->schema([
                            Placeholder::make('review_type')
                                ->label('Jenis Usulan')
                                ->content(fn (Get $get): string => StandardImprovementProposalType::tryFrom((string) $get('proposal_type'))?->getLabel() ?? '-'),
                            Placeholder::make('review_title')
                                ->label('Judul')
                                ->content(fn (Get $get): string => (string) ($get('title') ?: '-')),
                        ]),
                ]),
        ];
    }

    private function createProposal(array $data, StandardImprovementProposalStatus $status): null
    {
        $proposal = StandardImprovementProposal::query()->create([
            ...$data,
            'status' => $status,
            'proposed_by' => auth()->id(),
        ]);

        Notification::make()
            ->success()
            ->title($status === StandardImprovementProposalStatus::Submitted ? 'Usulan berhasil diajukan.' : 'Draf usulan berhasil dibuat.')
            ->send();

        $this->redirect(ViewStandardImprovementProposal::getUrl(['proposal' => $proposal]), navigate: true);

        return null;
    }

    private function canCreateProposal(): bool
    {
        return (bool) auth()->user()?->hasAnyRole(['super_admin', 'admin_lpm']);
    }
}
