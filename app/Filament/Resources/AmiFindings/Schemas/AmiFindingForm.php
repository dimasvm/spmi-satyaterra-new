<?php

namespace App\Filament\Resources\AmiFindings\Schemas;

use App\Enums\AmiAuditStatus;
use App\Enums\AmiFindingCategory;
use App\Enums\AmiFindingStatus;
use App\Models\AmiAudit;
use App\Models\AmiChecklist;
use App\Models\StandardIndicator;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class AmiFindingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(static::components());
    }

    public static function components(
        ?int $auditId = null,
        ?int $checklistId = null,
        bool $includeAuditField = true,
        bool $includeChecklistField = true,
        bool $includeIndicatorField = true,
    ): array {
        return [
            Section::make('Konteks Audit')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            $includeAuditField
                                ? Select::make('ami_audit_id')
                                    ->label('Audit AMI')
                                    ->options(fn (): array => static::auditOptions())
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->disabled(fn (string $operation): bool => $operation === 'edit')
                                    ->afterStateUpdated(function (Set $set): void {
                                        $set('ami_checklist_id', null);
                                        $set('standard_indicator_id', null);
                                    })
                                : Hidden::make('ami_audit_id')
                                    ->default($auditId)
                                    ->dehydrated(),
                            TextInput::make('finding_number')
                                ->label('Nomor Temuan')
                                ->placeholder('Otomatis saat disimpan')
                                ->disabled()
                                ->maxLength(255),
                            $includeChecklistField
                                ? Select::make('ami_checklist_id')
                                    ->label('Checklist')
                                    ->options(fn (Get $get): array => static::checklistOptions((int) $get('ami_audit_id')))
                                    ->searchable()
                                    ->preload()
                                    ->nullable()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, mixed $state): void {
                                        $checklist = filled($state) ? AmiChecklist::query()->find($state) : null;

                                        $set('standard_indicator_id', $checklist?->standard_indicator_id);
                                    })
                                : Hidden::make('ami_checklist_id')
                                    ->default($checklistId)
                                    ->dehydrated(),
                            $includeIndicatorField
                                ? Select::make('standard_indicator_id')
                                    ->label('Indikator')
                                    ->options(fn (): array => static::indicatorOptions())
                                    ->searchable()
                                    ->preload()
                                    ->nullable()
                                : Hidden::make('standard_indicator_id')
                                    ->default(fn (): ?int => $checklistId === null
                                        ? null
                                        : AmiChecklist::query()->whereKey($checklistId)->value('standard_indicator_id'))
                                    ->dehydrated(),
                        ]),
                ])
                ->columnSpanFull(),
            Section::make('Temuan')
                ->schema([
                    Grid::make(3)
                        ->schema([
                            Select::make('category')
                                ->label('Kategori')
                                ->options(AmiFindingCategory::class)
                                ->required()
                                ->live(),
                            DatePicker::make('due_date')
                                ->label('Batas Waktu')
                                ->required(fn (Get $get): bool => static::requiresCorrectiveAction($get('category'))),
                            Select::make('status')
                                ->label('Status')
                                ->options(AmiFindingStatus::class)
                                ->default(AmiFindingStatus::Open->value)
                                ->required(),
                        ]),
                    Textarea::make('description')
                        ->label('Deskripsi')
                        ->rows(5)
                        ->required()
                        ->columnSpanFull(),
                    Textarea::make('root_cause')
                        ->label('Akar Masalah')
                        ->rows(4)
                        ->nullable()
                        ->columnSpanFull(),
                    Textarea::make('recommendation')
                        ->label('Rekomendasi')
                        ->rows(4)
                        ->required(fn (Get $get): bool => static::requiresCorrectiveAction($get('category')))
                        ->columnSpanFull(),
                    Hidden::make('created_by')
                        ->default(auth()->id())
                        ->dehydrated(),
                ])
                ->columnSpanFull(),
        ];
    }

    private static function requiresCorrectiveAction(mixed $category): bool
    {
        $value = $category instanceof AmiFindingCategory ? $category->value : $category;

        return in_array($value, [
            AmiFindingCategory::Minor->value,
            AmiFindingCategory::Major->value,
        ], true);
    }

    private static function auditOptions(): array
    {
        return AmiAudit::query()
            ->with(['amiPeriod', 'auditeeUnit'])
            ->when(auth()->user()?->hasRole('auditor'), fn (Builder $query): Builder => $query
                ->where('status', '!=', AmiAuditStatus::Finalized->value)
                ->whereHas('auditorAssignments', fn (Builder $auditorQuery): Builder => $auditorQuery
                    ->where('user_id', auth()->id())))
            ->orderByDesc('scheduled_date')
            ->get()
            ->mapWithKeys(fn (AmiAudit $audit): array => [
                $audit->id => trim(($audit->amiPeriod?->name ?? 'Audit AMI').' - '.($audit->auditeeUnit?->name ?? 'Unit')),
            ])
            ->all();
    }

    private static function checklistOptions(int $auditId): array
    {
        if ($auditId < 1) {
            return [];
        }

        return AmiChecklist::query()
            ->with('standardIndicator')
            ->where('ami_audit_id', $auditId)
            ->get()
            ->mapWithKeys(fn (AmiChecklist $checklist): array => [
                $checklist->id => trim(($checklist->standardIndicator?->code ?? 'Checklist').' - '.str($checklist->standardIndicator?->statement ?? '')->limit(80)),
            ])
            ->all();
    }

    private static function indicatorOptions(): array
    {
        return StandardIndicator::query()
            ->orderBy('code')
            ->get()
            ->mapWithKeys(fn (StandardIndicator $indicator): array => [
                $indicator->id => trim($indicator->code.' - '.str($indicator->statement)->limit(90)),
            ])
            ->all();
    }
}
