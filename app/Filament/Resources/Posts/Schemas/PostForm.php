<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PostForm
{
    /**
     * Get the form schema.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Wrapping the fields in a visual block card
                Section::make('Post Information')
                    ->schema([
                        // The post title
                        TextInput::make('title')
                            ->required()
                            ->string()
                            ->minLength(3)
                            ->maxLength(255)
                            ->columnSpanFull(),

                        // The post content
                        Textarea::make('content')
                            ->required()
                            ->string()
                            ->minLength(20)
                            ->rows(12)
                            ->columnSpanFull(),

                        // The post publish status
                        Toggle::make('is_published')
                            ->required()
                            ->rules(['boolean']),

                        // The post image
                        FileUpload::make('image')
                            ->directory('posts')
                            ->disk('public')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'])
                            ->maxSize(2048)
                            ->nullable()
                            ->columnSpanFull(),

                        // The post author
                        Select::make('user_id')
                            ->relationship('user', 'email')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),

                        // The post tags
                        Select::make('tags') // The name must strictly match the name of the communication method in the model (tags)
                            ->relationship(titleAttribute: 'name') // 'name' is the column with the tag name in the tags table
                            ->multiple() // Allows to select multiple tags at the same time
                            ->searchable() // Adds a live search for already existing tags
                            ->preload() // Loads existing tags into a drop-down list (convenient if there are < 100 tags)
                            ->maxItems(7) // Limit: maximum of 7 tags
                            ->validationMessages([
                                'max' => 'You cannot select more than 7 tags for one post.',
                            ])
                            ->createOptionForm([ // The "+" button next to the select to create a new tag on the fly
                                TextInput::make('name')
                                    ->required()
                                    ->unique('tags', 'name') // Protection against duplicate tags in the database
                                    ->string()
                                    ->minLength(2)
                                    ->maxLength(50)
                                    ->regex('/^[a-zA-Z0-9\s]+$/'),
                            ])
                            ->nestedRecursiveRules([ // Validation rules for every element of tags
                                'integer', // IDs only & Tag IDs must be integers
                            ])
                            ->label('Post tags'),
                ])->columns(1), // Display fields in 1 column
            ]);
    }
}
