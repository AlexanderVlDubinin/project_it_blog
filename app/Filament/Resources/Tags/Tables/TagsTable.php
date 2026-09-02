<?php

namespace App\Filament\Resources\Tags\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TagsTable
{
    /**
     * Configure the tags table.
     */
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // Tag ID
                TextColumn::make('id')
                    ->searchable()
                    ->sortable(),

                // Tag Name
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                // Created At
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // Updated At
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(), // can Edit
                DeleteAction::make(), // can Delete
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(), // can Delete
                ]),
            ]);
    }
}
