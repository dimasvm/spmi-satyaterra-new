<?php

namespace App\Filament\Resources\AmiAudits\Schemas;

use App\Enums\AmiAuditStatus;
use App\Models\AmiAudit;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class AmiAuditForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Penugasan Audit Unit')
                    ->schema([
                        Select::make('ami_period_id')
                            ->label('Periode AMI')
                            ->relationship('amiPeriod', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('auditee_unit_id')
                            ->label('Unit Auditee')
                            ->relationship('auditeeUnit', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->scopedUnique(
                                model: AmiAudit::class,
                                column: 'auditee_unit_id',
                                ignoreRecord: true,
                                modifyQueryUsing: fn (Builder $query, Get $get): Builder => $query->where('ami_period_id', $get('ami_period_id')),
                            ),
                        DatePicker::make('scheduled_date')
                            ->label('Jadwal Audit'),
                        Select::make('status')
                            ->label('Status')
                            ->options(AmiAuditStatus::class)
                            ->default(AmiAuditStatus::Planned->value)
                            ->required(),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
