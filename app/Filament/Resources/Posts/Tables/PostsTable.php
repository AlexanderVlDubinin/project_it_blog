<?php

namespace App\Filament\Resources\Posts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // 1. Adding withCount to the base query of the table for optimization
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount([
                'reactions as likes_count' => fn ($q) => $q->where('is_like', true),
            ]))
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_published')
                    ->boolean()
                    ->sortable()
                    ->alignCenter(),
                //ImageColumn::make('image'),
                ImageColumn::make('image')
                    ->disk('public')
                    ->square(),
                /*IconColumn::make('image') // icons
                    ->label('Image')
                    ->boolean() // Turns any filled-in value to true (if not null)
                    ->trueIcon('heroicon-o-check-circle')  // The green checkmark icon
                    ->falseIcon('heroicon-o-x-circle')     // The red cross icon
                    ->trueColor('success')
                    ->falseColor('danger'),*/
                TextColumn::make('user.email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('likes_count')
                    ->label('Likes')
                    ->icon('heroicon-o-heart') // Heart icon
                    ->color('danger')          // Red color of the text/icon
                    ->badge()                  // Display as a badge
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
