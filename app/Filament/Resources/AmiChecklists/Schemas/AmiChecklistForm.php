<?php

namespace App\Filament\Resources\AmiChecklists\Schemas;

use App\Enums\AmiAssessmentResult;
use App\Models\AmiChecklist;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class AmiChecklistForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Assessment Checklist')
                    ->schema([
                        Select::make('ami_audit_id')
                            ->label('Audit AMI')
                            ->relationship(
                                name: 'audit',
                                titleAttribute: 'id',
                                modifyQueryUsing: fn (Builder $query): Builder => $query->with(['amiPeriod', 'auditeeUnit']),
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record): string => trim(($record->amiPeriod?->name ?? 'Audit AMI').' - '.($record->auditeeUnit?->name ?? 'Unit')))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->disabled(fn (string $operation): bool => $operation === 'edit'),
                        Select::make('standard_indicator_id')
                            ->label('Indikator')
                            ->relationship('standardIndicator', 'statement')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->rules([
                                fn (Get $get, ?AmiChecklist $record): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get, $record): void {
                                    if (blank($get('ami_audit_id')) || blank($value)) {
                                        return;
                                    }

                                    $exists = AmiChecklist::query()
                                        ->where('ami_audit_id', $get('ami_audit_id'))
                                        ->where('standard_indicator_id', $value)
                                        ->when($record !== null, fn (Builder $query): Builder => $query->whereKeyNot($record->getKey()))
                                        ->exists();

                                    if ($exists) {
                                        $fail('Indikator ini sudah ada pada checklist audit yang sama.');
                                    }
                                },
                            ]),
                        Select::make('assessment_result')
                            ->label('Hasil Assessment')
                            ->options(AmiAssessmentResult::class)
                            ->placeholder('Belum dinilai'),
                        Textarea::make('auditor_notes')
                            ->label('Catatan Auditor')
                            ->rows(5)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
