<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('unit.name')
                    ->label('Unit')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge(),
                TextColumn::make('email_verified_at')
                    ->label('Email Terverifikasi')
                    ->dateTime()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('unit')
                    ->label('Unit')
                    ->relationship('unit', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('roles')
                    ->label('Role')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_active')
                    ->label('Aktif'),
            ])
            ->emptyStateHeading('Belum ada user')
            ->emptyStateDescription('Tambahkan pengguna dan hubungkan dengan unit serta role yang sesuai.')
            ->emptyStateIcon(Heroicon::OutlinedUsers)
            ->defaultSort('name')
            ->recordActions([
                Action::make('impersonate')
                    ->label('Masuk')
                    ->icon(Heroicon::OutlinedArrowRightOnRectangle)
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading(fn (User $record): string => "Masuk sebagai {$record->name}?")
                    ->modalDescription('Session Anda akan berpindah ke pengguna ini.')
                    ->visible(fn (User $record): bool => auth()->user()?->can('users.impersonate')
                        && auth()->id() !== $record->id
                        && $record->is_active)
                    ->action(function (User $record) {
                        session()->put('impersonator_id', auth()->id());

                        Auth::login($record);
                        session()->regenerate();

                        return redirect()->to('/admin');
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
