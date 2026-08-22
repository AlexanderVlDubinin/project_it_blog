<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enum\UserRole;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('role')
                    ->badge()
                    ->searchable(),
            ])
            ->filters([
                // Filters can be added here (for example, administrators only)
                SelectFilter::make('role')
                    ->options([
                        'admin' => 'Admin',
                        'moderator' => 'Moderator',
                        'author' => 'Author',
                        'user' => 'User',
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    // disable edit action for moderator
                    ->disabled(fn (User $record): bool =>
                        auth()->user()->role === UserRole::MODERATOR && $record->role === UserRole::ADMIN
                    ),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        // bulk delete is visible only for admin
                        ->visible(fn (): bool => auth()->user()->role === UserRole::ADMIN),
                ]),
            ]);
    }
}
