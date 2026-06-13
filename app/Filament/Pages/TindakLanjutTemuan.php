<?php

namespace App\Filament\Pages;

use App\Enums\CorrectiveActionStatus;
use App\Filament\Resources\CorrectiveActions\CorrectiveActionResource;
use App\Models\AmiFinding;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class TindakLanjutTemuan extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'Pengendalian';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Tindak Lanjut';

    protected static ?string $title = 'Tindak Lanjut Temuan';

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.tindak-lanjut-temuan';

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->hasRole('unit_pic')
            && auth()->user()?->can('corrective-actions.view')
            && auth()->user()?->unit_id !== null;
    }

    public function table(Table $table): Table
    {
        $user = auth()->user();

        return $table
            ->query(AmiFinding::query()
                ->with([
                    'audit.auditeeUnit',
                    'standardIndicator',
                    'latestCorrectiveAction.picUser',
                ])
                ->when($user !== null, fn (Builder $query): Builder => $query->visibleToUser($user)))
            ->columns([
                TextColumn::make('finding_number')
                    ->label('Nomor Temuan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('standardIndicator.code')
                    ->label('Indikator')
                    ->placeholder('-'),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Temuan')
                    ->limit(70)
                    ->wrap()
                    ->searchable(),
                TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date('d M Y')
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status Temuan')
                    ->badge()
                    ->sortable(),
                TextColumn::make('latestCorrectiveAction.status')
                    ->label('Status Tindak Lanjut')
                    ->badge()
                    ->placeholder('Belum Ada'),
                TextColumn::make('latestCorrectiveAction.target_date')
                    ->label('Target')
                    ->date('d M Y')
                    ->placeholder('-'),
            ])
            ->emptyStateHeading('Belum ada temuan yang perlu ditindaklanjuti')
            ->emptyStateDescription('Temuan unit akan muncul di sini setelah audit selesai dan temuan dibuka untuk tindak lanjut.')
            ->emptyStateIcon(Heroicon::OutlinedClipboardDocumentList)
            ->recordActions([
                Action::make('followUp')
                    ->label(fn (AmiFinding $record): string => $record->latestCorrectiveAction === null ? 'Buat Tindak Lanjut' : 'Buka Tindak Lanjut')
                    ->icon(Heroicon::OutlinedArrowRight)
                    ->color(fn (AmiFinding $record): string => $record->latestCorrectiveAction === null ? 'primary' : 'gray')
                    ->url(function (AmiFinding $record): string {
                        $correctiveAction = $record->latestCorrectiveAction;

                        if ($correctiveAction === null) {
                            return CorrectiveActionResource::getUrl('create').'?ami_finding_id='.$record->getKey();
                        }

                        $page = $correctiveAction->status === CorrectiveActionStatus::Accepted ? 'view' : 'edit';

                        return CorrectiveActionResource::getUrl($page, ['record' => $correctiveAction]);
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
