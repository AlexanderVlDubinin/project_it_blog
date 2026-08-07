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
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Wrapping the fields in a visual block card
                Section::make('Post Information')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->columnSpanFull(),
                        Textarea::make('content')
                            ->required()
                            ->rows(12)
                            ->columnSpanFull(),
                        Toggle::make('is_published')
                            ->required(),
                        FileUpload::make('image')
                            ->directory('posts')
                            ->disk('public')
                            ->image()
                            ->columnSpanFull(),
                        Select::make('user_id')
                            ->relationship('user', 'email')
                            ->columnSpanFull(),
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
                                ->unique('tags', 'name'), // Protection against duplicate tags in the database
                        ])
                            ->label('Post tags'),
                ])->columns(1), // Display fields in 1 column
            ]);
    }
}
