<?php

namespace App\Actions;

use App\Enums\IndicatorAssignmentPriority;
use App\Enums\IndicatorAssignmentStatus;
use App\Models\IndicatorUnitAssignment;
use App\Models\StandardIndicator;
use App\Models\User;
use Carbon\CarbonInterface;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class AssignIndicatorsToUnits
{
    /**
     * @param  iterable<int, StandardIndicator>  $indicators
     * @param  array<int, int|string>  $unitIds
     * @return array{created: int, skipped: int}
     */
    public function handle(
        iterable $indicators,
        array $unitIds,
        int|string $spmiPeriodId,
        int|string|null $primaryPicUnitId = null,
        ?string $dueDate = null,
        IndicatorAssignmentStatus|string $status = IndicatorAssignmentStatus::Assigned,
        IndicatorAssignmentPriority|string $priority = IndicatorAssignmentPriority::Normal,
        ?string $notes = null,
        ?int $assignedBy = null,
        ?CarbonInterface $assignedAt = null,
    ): array {
        $createdCount = 0;
        $skippedCount = 0;
        $statusValue = $status instanceof IndicatorAssignmentStatus ? $status->value : $status;
        $priorityValue = $priority instanceof IndicatorAssignmentPriority ? $priority->value : $priority;
        $assignedAt ??= now();

        DB::transaction(function () use (
            $indicators,
            $unitIds,
            $spmiPeriodId,
            $primaryPicUnitId,
            $dueDate,
            $statusValue,
            $priorityValue,
            $notes,
            $assignedBy,
            $assignedAt,
            &$createdCount,
            &$skippedCount,
        ): void {
            foreach ($indicators as $indicator) {
                foreach ($unitIds as $unitId) {
                    $assignment = IndicatorUnitAssignment::query()->firstOrCreate(
                        [
                            'standard_indicator_id' => $indicator->id,
                            'unit_id' => $unitId,
                            'spmi_period_id' => $spmiPeriodId,
                        ],
                        [
                            'due_date' => $dueDate,
                            'status' => $statusValue,
                            'is_primary_pic' => (string) $primaryPicUnitId === (string) $unitId,
                            'priority' => $priorityValue,
                            'notes' => $notes,
                            'assigned_by' => $assignedBy,
                            'assigned_at' => $assignedAt,
                        ],
                    );

                    if (! $assignment->wasRecentlyCreated) {
                        $skippedCount++;

                        continue;
                    }

                    $createdCount++;


                    // Create Notification
                    $recipients = User::query()->where('unit_id', $unitId)->get();

                    foreach ($recipients as $recipient) {
                        $qualityStandard = $indicator->qualityStandard;
                        $title = "Penugasan $qualityStandard->name";
                        $desc = $indicator->statement;

                        $this->triggerNotification($recipient, $title, $desc);
                    }

                }
            }
        });

        return [
            'created' => $createdCount,
            'skipped' => $skippedCount,
        ];
    }

    public function createReminderEvent(IndicatorUnitAssignment $assignment, ?int $actorId = null): void
    {
        $event = 'reminder_sent';
        $description = 'Reminder penugasan dikirim.';

        $assignment->events()->create([
            'actor_id' => $actorId,
            'event' => $event,
            'description' => $description,
            'occurred_at' => now(),
        ]);
    }

    private function triggerNotification(User $recipent, ?string $title, ?string $description)
    {
        Notification::make()
            ->title($title ?? 'Penugasan Indikator Mutu')
            ->warning()
            ->body($description ?? 'Penugasan telah ditugaskan ke unit anda')
            ->sendToDatabase($recipent, isEventDispatched: true);
    }
}
