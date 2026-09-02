<?php

namespace App\Filament\Resources\Tags\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TagForm
{
    /**
     * Configure the tag form.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Tag Name
                TextInput::make('name')
                    ->required()
                    ->string()
                    ->minLength(2)
                    ->maxLength(50)
                    ->rules([
                        'not_regex:/[^a-zA-Z0-9\s]/', // Allow only letters, numbers, and spaces
                    ])
                    ->validationMessages([
                        'not_regex' => 'The tag name must contain only letters, numbers, and spaces.',
                    ]),
            ]);
    }
}
