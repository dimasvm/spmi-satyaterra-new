<?php

namespace App\Filament\Resources\IndicatorAchievements\Pages;

use App\Enums\AchievementReviewStatus;
use App\Enums\IndicatorAssignmentStatus;
use App\Enums\SubmissionStatus;
use App\Filament\Resources\IndicatorAchievements\IndicatorAchievementResource;
use App\Models\AchievementReview;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditIndicatorAchievement extends EditRecord
{
    protected static string $resource = IndicatorAchievementResource::class;

    protected string $view = 'filament.resources.indicator-achievements.pages.edit-indicator-achievement';

    protected string $header = 'Isi Capaian';

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label('Simpan Draf'),
            Action::make('submitAchievement')
                ->label('Submit')
                ->color('primary')
                ->requiresConfirmation()
                ->action('submitAchievement')
                ->visible(fn (): bool => $this->canSubmitAchievement()),
            $this->getCancelFormAction(),
        ];
    }

    public function submitAchievement(): void
    {
        $this->save(shouldRedirect: false, shouldSendSavedNotification: false);

        DB::transaction(function (): void {
            $achievement = $this->getRecord();

            $achievement->update([
                'submission_status' => SubmissionStatus::Submitted,
                'submitted_at' => now(),
                'submitted_by' => auth()->id(),
            ]);

            $achievement->assignment()->update([
                'status' => IndicatorAssignmentStatus::Submitted,
            ]);

            $achievement->reviews()->create([
                'reviewer_id' => null,
                'status' => AchievementReviewStatus::Pending,
                'notes' => null,
                'reviewed_at' => null,
            ]);
        });

        Notification::make()
            ->success()
            ->title('Capaian indikator berhasil disubmit.')
            ->send();

        $this->redirect($this->getResource()::getUrl('index'));
    }

    private function canSubmitAchievement(): bool
    {
        return in_array($this->getRecord()->submission_status, [
            SubmissionStatus::Draft,
            SubmissionStatus::Returned,
        ], true);
    }

    public function targetSummary(): string
    {
        $indicator = $this->getRecord()->standard_indicator;

        if ($indicator === null) {
            return '-';
        }

        return trim(implode(' ', array_filter([
            $indicator->target_operator?->value,
            (float) $indicator->target_value,
            $indicator->target_unit,
        ]))) ?: '-';
    }

    public function latestFinalReview(): ?AchievementReview
    {
        return $this->getRecord()
            ->reviews
            ->whereNotNull('reviewed_at')
            ->sortByDesc('reviewed_at')
            ->first();
    }

    protected function afterSave(): void
    {
        if (! in_array($this->getRecord()->submission_status, [
            SubmissionStatus::Draft,
            SubmissionStatus::Returned,
        ], true)) {
            return;
        }

        if (! in_array($this->getRecord()->assignment?->status, [
            IndicatorAssignmentStatus::Assigned,
            IndicatorAssignmentStatus::Returned,
        ], true)) {
            return;
        }

        $this->getRecord()->assignment()->update([
            'status' => IndicatorAssignmentStatus::InProgress,
        ]);
    }
}
