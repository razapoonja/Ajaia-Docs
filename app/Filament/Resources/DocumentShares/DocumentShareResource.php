<?php

namespace App\Filament\Resources\DocumentShares;

use App\Enums\ShareRole;
use App\Filament\Resources\DocumentShares\Pages\ManageDocumentShares;
use App\Models\DocumentShare;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DocumentShareResource extends Resource
{
    protected static ?string $model = DocumentShare::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShare;

    protected static ?string $modelLabel = 'share';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('document_id')->relationship('document', 'title')->required()->searchable()->preload(),
            Select::make('user_id')->relationship('user', 'name')->required()->searchable()->preload(),
            Select::make('role')
                ->options(collect(ShareRole::cases())->mapWithKeys(fn (ShareRole $role) => [$role->value => $role->label()]))
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['document:id,title,owner_id', 'document.owner:id,name', 'user:id,name']))
            ->columns([
                TextColumn::make('document.title')->label('Document')->searchable()->sortable(),
                TextColumn::make('document.owner.name')->label('Owner'),
                TextColumn::make('user.name')->label('Shared with')->searchable()->sortable(),
                TextColumn::make('role')->badge()->formatStateUsing(fn (ShareRole $state) => $state->label()),
                TextColumn::make('created_at')->since()->label('Granted'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('role')->options(['viewer' => 'Can view', 'editor' => 'Can edit']),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageDocumentShares::route('/')];
    }
}
