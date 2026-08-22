<?php

namespace App\Filament\Resources\Posts\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Comment Information')
                    ->schema([
                        Textarea::make('body')
                            ->required()
                            ->string()
                            ->minLength(2)
                            ->maxLength(2000)
                            ->columnSpanFull(),
                        Select::make('parent_id')
                            ->nullable()
                            ->relationship(
                                name: 'parent',
                                titleAttribute: 'body',
                                modifyQueryUsing: function (Builder $query, RelationManager $livewire, Get $get) {
                                    // 1. Obtain the ID of the current post via the parent record of the Livewire components
                                    $postId = $livewire->getOwnerRecord()->id;

                                    // 2. The current comment ID (if in editing mode, not creation mode)
                                    $currentCommentId = $get('id');

                                    return $query
                                        ->with(['user']) // authors
                                        ->where('post_id', $postId) // Filter: only comments on the CURRENT post
                                        ->when($currentCommentId, function ($q) use ($currentCommentId) {
                                            return $q->where('id', '!=', $currentCommentId); // Exclude the link to itself
                                        })
                                        ->select(['id', 'body', 'user_id', 'is_deleted', 'deletion_reason']); // Safe sampling for Strict Mode
                                }
                            )
                            ->getOptionLabelFromRecordUsing(function ($record) {
                                $author = $record->user?->name ?? 'Anonymous';
                                $text = Str::limit($record->body, 40);

                                return "{$author}: \"{$text}\"";
                            })
                            ->searchable(['body'])
                            ->preload()
                            ->placeholder('Root comment (no parent)'),
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->label('Author')
                            ->nullable() // Allows NULL to be written to the database
                            ->placeholder('Anonymous (Leave it blank)'), // Hint in the drop-down list
                        Toggle::make('is_deleted')
                            ->required(),
                        TextInput::make('deletion_reason'),
                    ])->columns(1), // Display fields in 1 column
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('body')
            // 1. Optimizing the communication request by adjusting the like and dislike counters.
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount([
                'reactions as likes_count' => fn ($q) => $q->where('is_like', true),
                'reactions as dislikes_count' => fn ($q) => $q->where('is_like', false),
            ]))
            ->columns([
                TextColumn::make('body')
                    ->label('Text')
                    ->limit(40),
                TextColumn::make('user.name')
                    ->label('Author')
                    ->default('Anonymous')
                    ->searchable()
                    ->sortable(),

                // The likes column
                TextColumn::make('likes_count')
                    ->label('👍')
                    ->alignCenter(),

                // The overall Rating (Balance) column
                TextColumn::make('rating')
                    ->label('Rating')
                    ->state(function ($record): int {
                        // Calculating the difference on the fly from uploaded counters
                        return ($record->likes_count ?? 0) - ($record->dislikes_count ?? 0);
                    })
                    ->badge()
                    // Dynamically changing the badge color: green (+), red (-), gray (0)
                    ->color(fn (int $state): string => match (true) {
                        $state > 0 => 'success',
                        $state < 0 => 'danger',
                        default => 'gray',
                    })
                    // Adding a plus sign for a positive rating
                    ->formatStateUsing(fn (int $state): string => $state > 0 ? "+{$state}" : (string)$state)
                    ->alignCenter(),

                // The dislikes column
                TextColumn::make('dislikes_count')
                    ->label('👎')
                    ->alignCenter(),

                TextColumn::make('parent.id')
                    ->default('-')
                    ->searchable()
                    ->alignCenter(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_deleted')
                    ->boolean()
                    ->alignCenter(),
                TextColumn::make('deletion_reason')
                    ->default('-')
                    ->searchable()
                    ->alignCenter(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                //AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                //DissociateAction::make(),
                //DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
