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
    /**
     * Configure the table.
     */
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // User ID column
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),

                // User Name column
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                // User Email column
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable()
                    ->sortable(),

                // User Email Verified At column
                TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),

                // User Created At column
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // User Updated At column
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // User Role column
                TextColumn::make('role')
                    ->badge()
                    ->searchable(),
            ])
            ->filters([
                // Filters (by role) can be added here (for example, administrators only)
                SelectFilter::make('role')
                    ->options([
                        'admin' => 'Admin',
                        'moderator' => 'Moderator',
                        'author' => 'Author',
                        'user' => 'User',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
