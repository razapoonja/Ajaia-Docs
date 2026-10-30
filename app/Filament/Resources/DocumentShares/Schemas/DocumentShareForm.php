<?php

namespace App\Filament\Resources\DocumentShares\Schemas;

use App\Enums\ShareRole;
use App\Models\Document;
use App\Models\DocumentShare;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Unique;

class DocumentShareForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Select::make('document_id')
                    ->label('Document')
                    ->relationship('document', 'title')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->disabledOn('edit'),
                Select::make('user_id')
                    ->label('Person')
                    ->relationship(
                        'user',
                        'name',
                        fn (Builder $query, Get $get) => $query->when(
                            $get('document_id'),
                            fn (Builder $q, $documentId) => $q->whereKeyNot(Document::query()->whereKey($documentId)->value('owner_id')),
                        ),
                    )
                    ->searchable(['name', 'email'])
                    ->preload()
                    ->required()
                    ->unique(
                        table: DocumentShare::class,
                        column: 'user_id',
                        ignoreRecord: true,
                        modifyRuleUsing: fn (Unique $rule, Get $get) => $rule->where('document_id', $get('document_id')),
                    )
                    ->validationMessages(['unique' => 'This person already has access to that document.'])
                    ->helperText('The document owner is excluded; owners always have full access.')
                    ->disabledOn('edit'),
                ToggleButtons::make('role')
                    ->label('Access')
                    ->options(ShareRole::class)
                    ->default(ShareRole::Viewer)
                    ->inline()
                    ->required(),
            ]);
    }
}
