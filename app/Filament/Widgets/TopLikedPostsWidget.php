<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use Filament\Actions\Action;
//use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
//use Illuminate\Database\Eloquent\Builder;

class TopLikedPostsWidget extends TableWidget
{
    // Widget sorting - the order of widget output
    protected static ?int $sort = 20;

    protected int | string | array $columnSpan = 'full';

    // The title of the card on the Dashboard
    protected static ?string $heading = 'Top 5 liked posts';


    public function table(Table $table): Table
    {
        return $table
            ->query(
                Post::query()
                    ->withCount([
                        'reactions as likes_count' => fn ($query) => $query->where('is_like', true)
                    ])
                    ->orderByDesc('likes_count')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('title')
                    ->label('Post title')
                    ->searchable()
                    ->limit(50),

                TextColumn::make('likes_count')
                    ->label('Likes')
                    ->icon('heroicon-o-heart')
                    ->color('danger')
                    ->badge()
                    ->alignCenter(),

                TextColumn::make('user.email')
                    ->label('Author'),

                ImageColumn::make('image')
                    ->disk('public')
                    ->square(),

                IconColumn::make('is_published')->boolean()->alignCenter(),

                TextColumn::make('created_at')
                    ->label('Published at')
                    ->date('d.m.Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            // [ADD THIS LINE] Disables the paged output of the Filament
            ->paginated(false)
            // Add the action of going to edit a post directly from the top
            ->recordActions([
                Action::make('view')
                    ->label('Open')
                    ->icon('heroicon-m-eye')
                    ->url(fn (Post $record): string => PostResource::getUrl('edit', ['record' => $record]))
                    ->openUrlInNewTab(),
            ]);

        // Base
        /*
        return $table
            ->query(fn (): Builder => Post::query())
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
                IconColumn::make('is_published')
                    ->boolean(),
                ImageColumn::make('image')
                    ->disk('public')
                    ->square(),
                //ImageColumn::make('image'),
                TextColumn::make('user.name')
                    ->searchable(),
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
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
        */
    }
}
