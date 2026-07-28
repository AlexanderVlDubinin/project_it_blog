<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enum\UserRole;
// use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Wrapping the fields in a visual block card
                Section::make('User Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->required()
                            ->maxLength(255),

                        TextInput::make('password')
                            ->password()
                            // A password is required only when creating a new user
                            ->required(fn (string $context): bool => $context === 'create')
                            // If the field was left empty during editing, do not overwrite the hash
                            ->dehydrated(fn ($state) => filled($state))
                            ->maxLength(255),

                        Select::make('role')
                            ->options(UserRole::class)
                            ->default('user')
                            ->required(),
                    ])->columns(1), // Display fields in 1 column
            ]);

        /*
        // default
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->required(),
                Select::make('role')
                    ->options(UserRole::class)
                    ->default('user')
                    ->required(),
            ]);
        */
    }
}
