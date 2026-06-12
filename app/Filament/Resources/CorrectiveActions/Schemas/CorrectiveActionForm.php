<?php

namespace App\Filament\Resources\CorrectiveActions\Schemas;

use App\Enums\CorrectiveActionStatus;
use App\Models\AmiFinding;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class CorrectiveActionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(static::components());
    }

    /**
     * @return array<int, mixed>
     */
    public static function components(?int $findingId = null, bool $includeFindingField = true): array
    {
        return [
            Section::make('Temuan')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            $includeFindingField
                                ? Select::make('ami_finding_id')
                                    ->label('Temuan AMI')
                                    ->options(fn (): array => static::findingOptions())
                                    ->default(fn (): ?int => request()->integer('ami_finding_id') ?: $findingId)
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->disabled(fn (string $operation): bool => $operation === 'edit')
                                : Hidden::make('ami_finding_id')
                                    ->default($findingId)
                                    ->dehydrated(),
                            Select::make('pic_user_id')
                                ->label('PIC')
                                ->options(fn (Get $get): array => static::picOptions((int) $get('ami_finding_id')))
                                ->searchable()
                                ->preload()
                                ->nullable(),
                        ]),
                ])
                ->columnSpanFull(),
            Section::make('Rencana Perbaikan')
                ->schema([
                    Textarea::make('root_cause_analysis')
                        ->label('Analisis Akar Masalah')
                        ->rows(4)
                        ->columnSpanFull(),
                    Textarea::make('action_plan')
                        ->label('Rencana Perbaikan')
                        ->rows(6)
                        ->required()
                        ->columnSpanFull(),
                    Grid::make(3)
                        ->schema([
                            DatePicker::make('target_date')
                                ->label('Target Selesai')
                                ->nullable(),
                            Select::make('status')
                                ->label('Status')
                                ->options(CorrectiveActionStatus::class)
                                ->default(CorrectiveActionStatus::Draft->value)
                                ->disabled()
                                ->dehydrated(false),
                            Hidden::make('submitted_by'),
                        ]),
                    Hidden::make('submitted_at'),
                ])
                ->columnSpanFull(),
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function findingOptions(): array
    {
        $user = auth()->user();

        if ($user === null) {
            return [];
        }

        return AmiFinding::query()
            ->with(['audit.auditeeUnit', 'standardIndicator'])
            ->visibleToUser($user)
            ->whereHas('audit')
            ->orderByDesc('created_at')
            ->get()
            ->mapWithKeys(fn (AmiFinding $finding): array => [
                $finding->id => trim(implode(' - ', array_filter([
                    $finding->finding_number,
                    $finding->audit?->auditeeUnit?->name,
                    $finding->standardIndicator?->code,
                    str($finding->description)->limit(70)->toString(),
                ]))),
            ])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public static function picOptions(int $findingId): array
    {
        $unitId = null;

        if ($findingId > 0) {
            $unitId = AmiFinding::query()
                ->whereKey($findingId)
                ->whereHas('audit')
                ->with('audit')
                ->first()
                ?->audit
                ?->auditee_unit_id;
        }

        $unitId ??= auth()->user()?->unit_id;

        return User::query()
            ->when($unitId !== null, fn (Builder $query): Builder => $query->where('unit_id', $unitId))
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }
}
