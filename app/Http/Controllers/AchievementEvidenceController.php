<?php

namespace App\Http\Controllers;

use App\Models\AchievementEvidence;
use App\Models\AmiAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AchievementEvidenceController extends Controller
{
    public function __invoke(Request $request, AchievementEvidence $evidence): StreamedResponse
    {
        $user = $request->user();
        $achievement = $evidence->achievement;
        $assignment = $achievement?->assignment;

        abort_unless($user !== null, 403);

        if (! $user?->can('indicator-achievements.review')
            && ! (method_exists($user, 'hasRole') && $user->hasRole('pimpinan'))
            && ! $this->isAssignedAmiAuditor($user->id, $assignment?->unit_id, $assignment?->spmi_period_id)
        ) {
            abort_unless(
                $user->can('indicator-achievements.view')
                    && $user->unit_id !== null
                    && $assignment?->unit_id === $user->unit_id,
                403,
            );
        }

        abort_if(blank($evidence->file_path), 404);

        $disk = Storage::disk(config('filament.default_filesystem_disk'));

        abort_unless($disk->exists($evidence->file_path), 404);

        $fileName = str_replace(['"', '\\'], '', $evidence->file_name ?: basename($evidence->file_path));

        return $disk->response($evidence->file_path, $fileName, [
            'Content-Disposition' => "inline; filename=\"{$fileName}\"",
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function isAssignedAmiAuditor(int $userId, ?int $unitId, ?int $spmiPeriodId): bool
    {
        if ($unitId === null || $spmiPeriodId === null) {
            return false;
        }

        return AmiAudit::query()
            ->where('auditee_unit_id', $unitId)
            ->whereHas('amiPeriod', fn ($query) => $query->where('spmi_period_id', $spmiPeriodId))
            ->whereHas('auditorAssignments', fn ($query) => $query->where('user_id', $userId))
            ->exists();
    }
}
