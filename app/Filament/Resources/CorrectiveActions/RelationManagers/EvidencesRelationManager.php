<?php

namespace App\Filament\Resources\CorrectiveActions\RelationManagers;

use App\Models\CorrectiveAction;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;

class EvidencesRelationManager extends RelationManager
{
    protected static string $relationship = 'evidences';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('file_path')
                    ->label('File Bukti')
                    ->directory('corrective-action-evidences')
                    ->visibility('private')
                    ->storeFileNamesIn('file_name')
                    ->acceptedFileTypes([
                        'application/pdf',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'image/jpeg',
                        'image/png',
                        'image/webp',
                    ])
                    ->maxSize(5120)
                    ->columnSpanFull(),
                TextInput::make('external_url')
                    ->label('Tautan Eksternal')
                    ->url()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(3)
                    ->columnSpanFull(),
                TextInput::make('file_name')
                    ->label('Nama File')
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('file_name')
            ->columns([
                TextColumn::make('file_name')
                    ->label('File')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('external_url')
                    ->label('Tautan')
                    ->url(fn (?string $state): ?string => $state)
                    ->openUrlInNewTab()
                    ->placeholder('-')
                    ->limit(40),
                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(50)
                    ->wrap()
                    ->placeholder('-'),
                TextColumn::make('uploadedBy.name')
                    ->label('Diunggah Oleh')
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('uploadEvidence')
                    ->label('Tambah Bukti')
                    ->icon(Heroicon::OutlinedArrowUpTray)
                    ->schema([
                        FileUpload::make('files')
                            ->label('File Bukti')
                            ->multiple()
                            ->directory('corrective-action-evidences')
                            ->visibility('private')
                            ->acceptedFileTypes([
                                'application/pdf',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'image/jpeg',
                                'image/png',
                                'image/webp',
                            ])
                            ->maxSize(5120)
                            ->columnSpanFull(),
                        TextInput::make('external_url')
                            ->label('Tautan Eksternal')
                            ->url()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (): bool => $this->canManageEvidence())
                    ->action(function (array $data): void {
                        /** @var CorrectiveAction $correctiveAction */
                        $correctiveAction = $this->getOwnerRecord();
                        $files = collect($data['files'] ?? [])->filter();

                        if ($files->isEmpty() && blank($data['external_url'] ?? null)) {
                            throw ValidationException::withMessages([
                                'files' => 'Unggah file atau isi tautan eksternal.',
                            ]);
                        }

                        $files->each(function (string $path) use ($correctiveAction, $data): void {
                            $correctiveAction->evidences()->create([
                                'file_path' => $path,
                                'file_name' => basename($path),
                                'external_url' => null,
                                'description' => $data['description'] ?? null,
                                'uploaded_by' => auth()->id(),
                            ]);
                        });

                        if (filled($data['external_url'] ?? null)) {
                            $correctiveAction->evidences()->create([
                                'file_path' => null,
                                'file_name' => null,
                                'external_url' => $data['external_url'],
                                'description' => $data['description'] ?? null,
                                'uploaded_by' => auth()->id(),
                            ]);
                        }

                        Notification::make()
                            ->success()
                            ->title('Bukti berhasil ditambahkan.')
                            ->send();
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn (): bool => $this->canManageEvidence())
                    ->mutateDataUsing(fn (array $data): array => $this->prepareEvidenceData($data)),
                DeleteAction::make()
                    ->visible(fn (): bool => $this->canManageEvidence()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => $this->canManageEvidence()),
                ])
                    ->visible(fn (): bool => $this->canManageEvidence()),
            ]);
    }

    private function canManageEvidence(): bool
    {
        return (bool) auth()->user()?->can('update', $this->getOwnerRecord());
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function prepareEvidenceData(array $data): array
    {
        $data['uploaded_by'] = auth()->id();

        if (filled($data['file_path'] ?? null)) {
            $data['external_url'] = null;
        }

        return $data;
    }
}
