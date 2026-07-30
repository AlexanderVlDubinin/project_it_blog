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
                ])->columns(1), // Display fields in 1 column
            ]);
    }
}
