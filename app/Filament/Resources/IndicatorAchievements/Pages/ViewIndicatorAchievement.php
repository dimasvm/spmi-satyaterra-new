<?php

namespace App\Filament\Resources\IndicatorAchievements\Pages;

use App\Enums\EvidenceFileType;
use App\Filament\Resources\IndicatorAchievements\IndicatorAchievementResource;
use App\Models\AchievementEvidence;
use App\Models\AchievementReview;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\URL;

class ViewIndicatorAchievement extends ViewRecord
{
    protected static string $resource = IndicatorAchievementResource::class;

    protected string $view = 'filament.resources.indicator-achievements.pages.view-indicator-achievement';

    public function getTitle(): string|Htmlable
    {
        return 'Detail Capaian Indikator';
    }

    public function getSubheading(): string|Htmlable|null
    {
        $indicator = $this->getRecord()->standard_indicator;
        $unit = $this->getRecord()->assignment?->unit?->name;

        return trim(implode(' - ', array_filter([
            $indicator?->code,
            $unit,
        ]))) ?: null;
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Isi / Ubah Capaian')
                ->icon(Heroicon::OutlinedPencil)
                ->visible(fn (): bool => auth()->user()?->can('update', $this->getRecord()) ?? false),
        ];
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

    public function evidenceUrl(AchievementEvidence $evidence): ?string
    {
        if ($evidence->file_type === EvidenceFileType::Link) {
            return $evidence->external_url;
        }

        if (blank($evidence->file_path)) {
            return null;
        }

        return URL::signedRoute('achievement-evidences.show', $evidence, absolute: false);
    }

    public function evidencePreviewType(AchievementEvidence $evidence): string
    {
        return match ($evidence->file_type) {
            EvidenceFileType::Image => 'image',
            EvidenceFileType::Pdf => 'pdf',
            EvidenceFileType::Link => 'link',
            default => 'file',
        };
    }

    public function evidenceName(AchievementEvidence $evidence): string
    {
        return $evidence->file_name
            ?: $evidence->external_url
            ?: basename((string) $evidence->file_path)
            ?: 'Bukti capaian';
    }

    public function latestFinalReview(): ?AchievementReview
    {
        return $this->getRecord()
            ->reviews
            ->whereNotNull('reviewed_at')
            ->sortByDesc('reviewed_at')
            ->first();
    }

    public function hasReviewResult(): bool
    {
        return $this->latestFinalReview() instanceof AchievementReview;
    }
}
