<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\Documents\DocumentResource;
use App\Models\Document;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class OwnedDocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'ownedDocuments';

    protected static ?string $title = 'Documents owned';

    protected static string|BackedEnum|null $icon = Heroicon::OutlinedDocumentText;

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        /** @var User $ownerRecord */
        return (string) $ownerRecord->ownedDocuments()->count();
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount('shares'))
            ->columns([
                TextColumn::make('title')->searchable()->sortable()->weight('medium')->limit(60),
                TextColumn::make('shares_count')
                    ->label('Shared with')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'primary' : 'gray')
                    ->sortable(),
                TextColumn::make('updated_at')->label('Last edited')->since()->dateTimeTooltip()->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->recordUrl(fn (Document $record): string => DocumentResource::getUrl('view', ['record' => $record]))
            ->recordActions([
                Action::make('open')
                    ->label('Open')
                    ->icon(Heroicon::OutlinedEye)
                    ->url(fn (Document $record): string => DocumentResource::getUrl('view', ['record' => $record])),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ])
            ->emptyStateIcon(Heroicon::OutlinedDocumentText)
            ->emptyStateHeading('No documents')
            ->emptyStateDescription('This person has not created any documents yet.');
    }
}
