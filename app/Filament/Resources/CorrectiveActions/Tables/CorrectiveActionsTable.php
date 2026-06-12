<?php

namespace App\Filament\Resources\CorrectiveActions\Tables;

use App\Enums\AmiFindingCategory;
use App\Enums\AmiFindingStatus;
use App\Enums\CorrectiveActionReviewStatus;
use App\Enums\CorrectiveActionStatus;
use App\Filament\Resources\CorrectiveActions\CorrectiveActionResource;
use App\Models\CorrectiveAction;
use App\Models\Unit;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CorrectiveActionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('finding.finding_number')
                    ->label('Nomor Temuan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('finding.audit.auditeeUnit.name')
                    ->label('Unit')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('finding.category')
                    ->label('Kategori')
                    ->badge()
                    ->sortable(),
                TextColumn::make('action_plan')
                    ->label('Rencana')
                    ->limit(60)
                    ->wrap()
                    ->searchable(),
                TextColumn::make('picUser.name')
                    ->label('PIC')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('target_date')
                    ->label('Target')
                    ->date('d M Y')
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('overdue_state')
                    ->label('Overdue')
                    ->state(fn (CorrectiveAction $record): ?string => $record->isOverdue() ? 'Overdue' : null)
                    ->badge()
                    ->color('danger')
                    ->placeholder('-'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('submitted_at')
                    ->label('Dikirim')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(CorrectiveActionStatus::class),
                SelectFilter::make('unit')
                    ->label('Unit')
                    ->options(fn (): array => Unit::query()
                        ->active()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->query(fn (Builder $query, array $data): Builder => filled($data['value'] ?? null)
                        ? $query->whereHas('finding.audit', fn (Builder $auditQuery): Builder => $auditQuery->where('auditee_unit_id', $data['value']))
                        : $query)
                    ->searchable()
                    ->preload(),
                SelectFilter::make('category')
                    ->label('Kategori Temuan')
                    ->options(AmiFindingCategory::class)
                    ->query(fn (Builder $query, array $data): Builder => filled($data['value'] ?? null)
                        ? $query->whereHas('finding', fn (Builder $findingQuery): Builder => $findingQuery->where('category', $data['value']))
                        : $query),
                Filter::make('overdue')
                    ->label('Overdue')
                    ->query(fn (Builder $query): Builder => $query
                        ->where('status', '!=', CorrectiveActionStatus::Accepted->value)
                        ->where(function (Builder $query): void {
                            $query
                                ->whereDate('target_date', '<', today())
                                ->orWhereHas('finding', fn (Builder $findingQuery): Builder => $findingQuery
                                    ->whereDate('due_date', '<', today())
                                    ->where('status', '!=', AmiFindingStatus::Closed->value));
                        })),
            ])
            ->emptyStateHeading('Belum ada tindak lanjut')
            ->emptyStateDescription('Tindak lanjut akan muncul setelah unit membuat rencana perbaikan dari temuan audit.')
            ->emptyStateIcon(Heroicon::OutlinedClipboardDocumentCheck)
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn (CorrectiveAction $record): bool => CorrectiveActionResource::canEdit($record)),
                static::submitVerificationAction(),
                static::acceptAction(),
                static::requestRevisionAction(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => CorrectiveActionResource::canDeleteAny()),
                ])
                    ->visible(fn (): bool => CorrectiveActionResource::canDeleteAny()),
            ]);
    }

    public static function submitVerificationAction(): Action
    {
        return Action::make('submitVerification')
            ->label('Submit Tindak Lanjut')
            ->icon(Heroicon::OutlinedPaperAirplane)
            ->color('primary')
            ->requiresConfirmation()
            ->visible(fn (CorrectiveAction $record): bool => auth()->user()?->can('submit', $record)
                && in_array($record->status, [CorrectiveActionStatus::Draft, CorrectiveActionStatus::NeedRevision], true))
            ->action(function (CorrectiveAction $record): void {
                $record->loadMissing(['finding', 'evidences']);
                static::validateSubmit($record);

                DB::transaction(function () use ($record): void {
                    $record->update([
                        'status' => CorrectiveActionStatus::Submitted,
                        'submitted_at' => now(),
                        'submitted_by' => auth()->id(),
                    ]);

                    $record->finding?->update([
                        'status' => AmiFindingStatus::WaitingVerification,
                    ]);
                });

                Notification::make()
                    ->success()
                    ->title('Tindak lanjut dikirim untuk verifikasi.')
                    ->send();
            });
    }

    public static function acceptAction(): Action
    {
        return Action::make('accept')
            ->label('Verifikasi')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->requiresConfirmation()
            ->visible(fn (CorrectiveAction $record): bool => auth()->user()?->can('review', $record)
                && in_array($record->status, [CorrectiveActionStatus::Submitted, CorrectiveActionStatus::InReview], true))
            ->action(function (CorrectiveAction $record): void {
                DB::transaction(function () use ($record): void {
                    $record->reviews()->create([
                        'reviewer_id' => auth()->id(),
                        'status' => CorrectiveActionReviewStatus::Accepted,
                        'reviewed_at' => now(),
                    ]);

                    $record->update([
                        'status' => CorrectiveActionStatus::Accepted,
                    ]);

                    $record->finding?->update([
                        'status' => AmiFindingStatus::Closed,
                    ]);
                });

                Notification::make()
                    ->success()
                    ->title('Tindak lanjut diterima.')
                    ->send();
            });
    }

    public static function requestRevisionAction(): Action
    {
        return Action::make('requestRevision')
            ->label('Minta Revisi')
            ->icon(Heroicon::OutlinedArrowPath)
            ->color('warning')
            ->modalHeading('Minta Revisi Tindak Lanjut')
            ->schema([
                Textarea::make('notes')
                    ->label('Catatan Revisi')
                    ->rows(4)
                    ->required(),
            ])
            ->visible(fn (CorrectiveAction $record): bool => auth()->user()?->can('review', $record)
                && in_array($record->status, [CorrectiveActionStatus::Submitted, CorrectiveActionStatus::InReview], true))
            ->action(function (CorrectiveAction $record, array $data): void {
                DB::transaction(function () use ($record, $data): void {
                    $record->reviews()->create([
                        'reviewer_id' => auth()->id(),
                        'status' => CorrectiveActionReviewStatus::NeedRevision,
                        'notes' => $data['notes'],
                        'reviewed_at' => now(),
                    ]);

                    $record->update([
                        'status' => CorrectiveActionStatus::NeedRevision,
                    ]);

                    $record->finding?->update([
                        'status' => AmiFindingStatus::NeedRevision,
                    ]);
                });

                Notification::make()
                    ->warning()
                    ->title('Revisi tindak lanjut diminta.')
                    ->send();
            });
    }

    private static function validateSubmit(CorrectiveAction $record): void
    {
        $messages = [];

        if (blank($record->root_cause_analysis)) {
            $messages['root_cause_analysis'] = 'Analisis akar masalah wajib diisi.';
        }

        if (blank($record->action_plan)) {
            $messages['action_plan'] = 'Rencana perbaikan wajib diisi.';
        }

        if (blank($record->pic_user_id)) {
            $messages['pic_user_id'] = 'PIC wajib dipilih.';
        }

        if (blank($record->target_date)) {
            $messages['target_date'] = 'Target selesai wajib diisi.';
        }

        if (in_array($record->finding?->category, [AmiFindingCategory::Minor, AmiFindingCategory::Major], true)
            && ! $record->evidences()->exists()) {
            $messages['evidences'] = 'Minimal satu bukti wajib diunggah untuk temuan minor atau mayor.';
        }

        if ($messages !== []) {
            throw ValidationException::withMessages($messages);
        }
    }
}
