<?php

namespace App\Filament\Resources\Documents\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Details')
                    ->description('Title and the account that owns this document.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(200)
                            ->default('Untitled document'),
                        Select::make('owner_id')
                            ->label('Owner')
                            ->relationship('owner', 'name')
                            ->searchable(['name', 'email'])
                            ->preload()
                            ->required(),
                    ]),
                Section::make('Content')
                    ->schema([
                        RichEditor::make('content')
                            ->hiddenLabel()
                            ->toolbarButtons([
                                ['bold', 'italic', 'underline'],
                                ['h1', 'h2', 'h3'],
                                ['bulletList', 'orderedList'],
                                ['undo', 'redo'],
                            ])
                            ->extraInputAttributes(['style' => 'min-height: 18rem']),
                    ]),
            ]);
    }
}
