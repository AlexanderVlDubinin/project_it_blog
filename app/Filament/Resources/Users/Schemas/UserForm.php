<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enum\UserRole;
// use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Password;

class UserForm
{
    /**
     * Configure the user form.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Wrapping the fields in a visual block card
                Section::make('User Information')
                    ->schema([
                        // User Name
                        TextInput::make('name')
                            ->required()
                            ->string()
                            ->minLength(3)
                            ->maxLength(255),

                        // User Email
                        TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->required()
                            ->string()
                            ->rules(['lowercase'])
                            ->unique()
                            ->maxLength(255),

                        // User Password
                        TextInput::make('password')
                            ->password()
                            // A password is required only when creating a new user
                            ->required(fn (string $context): bool => $context === 'create')
                            // If the field was left empty during editing, do not overwrite the hash
                            ->dehydrated(fn ($state) => filled($state))
                            ->rules([
                                'confirmed',
                                Password::defaults(),
                            ])
                            ->maxLength(255),

                        // User Password Confirmation
                        TextInput::make('password_confirmation')
                            ->label('Password confirmation')
                            ->password()
                            ->revealable()
                            ->required(fn ($get) => filled($get('password'))) // required only if password is filled
                            ->dehydrated(false), // do not save to database

                        // User Role
                        Select::make('role')
                            //->options(UserRole::class)
                            ->options(function (): array {
                                $roles = [
                                    'admin' => 'Admin',
                                    'moderator' => 'Moderator',
                                    'author' => 'Author',
                                    'user' => 'User',
                                ];

                                if (auth()->user()?->role === UserRole::MODERATOR) {
                                    unset($roles['admin']); // remove admin for moderators
                                }

                                return $roles;
                            })
                            ->default('user')
                            ->required(),
                    ])->columns(1), // Display fields in 1 column
            ]);
    }
}
