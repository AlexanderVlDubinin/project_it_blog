<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Enum\UserRole;
use App\Models\Post;
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
use Illuminate\Support\Collection;

class PostsTable
{
    /**
     * Get the table configuration.
     */
    public static function configure(Table $table): Table
    {
        return $table
            // 1. Adding withCount to the base query of the table for optimization
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount([
                'reactions as likes_count' => fn ($q) => $q->where('is_like', true),
            ]))
            ->columns([
                // The post title column
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                // The post publish status column
                IconColumn::make('is_published')
                    ->boolean()
                    ->sortable()
                    ->alignCenter(),

                // The post image column
                ImageColumn::make('image')
                    ->disk('public')
                    ->square(),
//                IconColumn::make('image') // icons
//                    ->label('Image')
//                    ->boolean() // Turns any filled-in value to true (if not null)
//                    ->trueIcon('heroicon-o-check-circle')  // The green checkmark icon
//                    ->falseIcon('heroicon-o-x-circle')     // The red cross icon
//                    ->trueColor('success')
//                    ->falseColor('danger'),

                // The post author column
                TextColumn::make('user.email')
                    ->searchable()
                    ->sortable(),

                // The post likes column
                TextColumn::make('likes_count')
                    ->label('Likes')
                    ->icon('heroicon-o-heart') // Heart icon
                    ->color('danger')          // Red color of the text/icon
                    ->badge()                  // Display as a badge
                    ->alignCenter()
                    ->sortable(),

                // The post deleted_at column
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // The post created_at column
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // The post updated_at column
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // The post ID column
                TextColumn::make('id')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                TrashedFilter::make(), // Add the Trashed filter
            ])
            ->recordActions([
                // Edit Action: moderator can not edit admin posts
                EditAction::make()
                    ->disabled(fn (Post $record): bool =>
                        auth()->user()->role === UserRole::MODERATOR && $record->user?->role === UserRole::ADMIN
                    ),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->authorizeIndividualRecords(),
                    // Force Delete Action: moderator can not force delete admin posts
                    ForceDeleteBulkAction::make()->authorizeIndividualRecords(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
