<?php

namespace App\Filament\Resources\Posts\RelationManagers;

use App\Enum\UserRole;
use App\Models\User;
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
    /**
     * The relationship name
     */
    protected static string $relationship = 'comments';

    /**
     * Get the form schema.
     */
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Comment Information')
                    ->schema([
                        // the comment body
                        Textarea::make('body')
                            ->required()
                            ->string()
                            ->minLength(2)
                            ->maxLength(2000)
                            ->columnSpanFull(),

                        // the parent comment - the comment that this comment is a reply to
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
                            ->getOptionLabelFromRecordUsing(function ($record) { // the label of the parent comment
                                $author = $record->user?->name ?? 'Anonymous';
                                $text = Str::limit($record->body, 40);

                                return "{$author}: \"{$text}\"";
                            })
                            ->searchable(['body'])
                            ->preload()
                            ->placeholder('Root comment (no parent)'),

                        // the author of the comment
                        Select::make('user_id')
                            //->relationship('user', 'name')
                            ->relationship(
                                name: 'user',
                                titleAttribute: 'name',
                                modifyQueryUsing: function (Builder $query) { // moderator cannot select an administrator as an author
                                    // If the form was opened by a moderator, remove all admins from the drop-down list
                                    if (auth()->user()?->role === UserRole::MODERATOR) {
                                        $query->where('role', '!=', UserRole::ADMIN);
                                    }
                                }
                            )
                            ->rules([ // Additional protection in case of substitution of the ID in the request
                                fn () => function (string $attribute, $value, \Closure $fail) {
                                    if (auth()->user()?->role === UserRole::MODERATOR && $value) {
                                        $chosenUser = User::query()->find($value);
                                        if ($chosenUser && $chosenUser->role === UserRole::ADMIN) {
                                            $fail('You cannot select an administrator as an author');
                                        }
                                    }
                                },
                            ])
                            ->label('Author')
                            ->nullable() // Allows NULL to be written to the database
                            ->preload() // preload users (first 50) for faster loading
                            ->searchable(['name', 'email']) // Search by name or email in selector
                            ->placeholder('Anonymous (Leave it blank)'), // Hint in the drop-down list

                        // the deletion status of the comment
                        Toggle::make('is_deleted')
                            ->required(),

                        // the reason for deletion
                        TextInput::make('deletion_reason'),
                    ])->columns(1), // Display fields in 1 column
            ]);
    }


    /**
     * Get the table schema.
     */
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
                // the comment text column
                TextColumn::make('body')
                    ->label('Text')
                    ->limit(40),

                // the author of the comment column
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

                // The parent comment (ID) column
                TextColumn::make('parent.id')
                    ->default('-')
                    ->searchable()
                    ->alignCenter(),

                // The creation date column
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // The update date column
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // The deletion status column
                IconColumn::make('is_deleted')
                    ->boolean()
                    ->alignCenter(),

                // The deletion reason column
                TextColumn::make('deletion_reason')
                    ->default('-')
                    ->searchable()
                    ->alignCenter(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(), // Create new comment
                //AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(), // Edit comment
                //DissociateAction::make(),
                //DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //DissociateBulkAction::make(),
                    DeleteBulkAction::make()->authorizeIndividualRecords(), // Delete selected comments
                ]),
            ]);
    }
}
