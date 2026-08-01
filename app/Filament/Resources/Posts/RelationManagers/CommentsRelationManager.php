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
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

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
                            ->columnSpanFull(),
                        Select::make('parent_id')
                            ->relationship('parent', 'id'),
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
            ->columns([
                TextColumn::make('body')
                    ->label('Text')
                    ->limit(40),
                TextColumn::make('user.name')
                    ->label('Author')
                    ->default('Anonymous')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('parent.id')
                    ->default('-')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_deleted')
                    ->boolean(),
                TextColumn::make('deletion_reason')
                    ->default('-')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                //DissociateAction::make(),
                //DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
