<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Enums\ShareRole;
use App\Filament\Resources\Documents\DocumentResource;
use App\Models\DocumentShare;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DocumentSharesRelationManager extends RelationManager
{
    protected static string $relationship = 'documentShares';

    protected static ?string $title = 'Shared with them';

    protected static string|BackedEnum|null $icon = Heroicon::OutlinedShare;

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        /** @var User $ownerRecord */
        return (string) $ownerRecord->documentShares()->count();
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            ToggleButtons::make('role')
                ->label('Access')
                ->options(ShareRole::class)
                ->inline()
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('document.title')
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['document:id,title,owner_id,updated_at', 'document.owner:id,name']))
            ->columns([
                TextColumn::make('document.title')
                    ->label('Document')
                    ->description(fn (DocumentShare $record): string => 'Owned by '.$record->document->owner->name)
                    ->url(fn (DocumentShare $record): string => DocumentResource::getUrl('view', ['record' => $record->document_id]))
                    ->searchable()
                    ->weight('medium'),
                TextColumn::make('role')->label('Access')->badge()->sortable(),
                TextColumn::make('created_at')->label('Granted')->since()->dateTimeTooltip()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('role')->options(ShareRole::class),
            ])
            ->recordActions([
                EditAction::make()->label('Change access')->modalHeading('Change access'),
                DeleteAction::make()->label('Revoke')->modalHeading('Revoke access'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()->label('Revoke selected')]),
            ])
            ->emptyStateIcon(Heroicon::OutlinedShare)
            ->emptyStateHeading('Nothing shared with them')
            ->emptyStateDescription('Documents other people share with this person will appear here.');
    }
}
