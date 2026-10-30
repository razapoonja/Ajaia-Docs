<?php

namespace App\Filament\Resources\Documents\Tables;

use App\Models\Document;
use App\Models\DocumentShare;
use App\Models\User;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class DocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('owner:id,name,email')->withCount('shares'))
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->limit(60)
                    ->tooltip(fn (Document $record): string => $record->title),
                TextColumn::make('owner.name')
                    ->label('Owner')
                    ->description(fn (Document $record): string => $record->owner->email)
                    ->searchable(['name', 'email'])
                    ->sortable(),
                TextColumn::make('shares_count')
                    ->label('Shared with')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'primary' : 'gray')
                    ->icon(Heroicon::OutlinedUserGroup)
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('updated_at')
                    ->label('Last edited')
                    ->since()
                    ->dateTimeTooltip()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                SelectFilter::make('owner_id')
                    ->label('Owner')
                    ->relationship('owner', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),
                TernaryFilter::make('shared')
                    ->label('Sharing')
                    ->placeholder('All documents')
                    ->trueLabel('Shared with others')
                    ->falseLabel('Private')
                    ->queries(
                        true: fn (Builder $query) => $query->has('shares'),
                        false: fn (Builder $query) => $query->doesntHave('shares'),
                    ),
                Filter::make('updated_between')
                    ->schema([
                        DatePicker::make('from')->label('Edited from'),
                        DatePicker::make('until')->label('Edited until'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $q, string $date) => $q->whereDate('updated_at', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $q, string $date) => $q->whereDate('updated_at', '<=', $date)))
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators[] = 'Edited from '.$data['from'];
                        }
                        if ($data['until'] ?? null) {
                            $indicators[] = 'Edited until '.$data['until'];
                        }

                        return $indicators;
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('transferOwnership')
                        ->label('Transfer ownership')
                        ->icon(Heroicon::OutlinedArrowsRightLeft)
                        ->color('warning')
                        ->schema([
                            Select::make('owner_id')
                                ->label('New owner')
                                ->options(fn (): array => User::query()->orderBy('name')->pluck('name', 'id')->all())
                                ->searchable()
                                ->required(),
                        ])
                        ->requiresConfirmation()
                        ->modalDescription('Existing shares are kept. If the new owner already had a share on a document, that share is removed because owners have full access.')
                        ->action(function (Collection $records, array $data): void {
                            $ownerId = (int) $data['owner_id'];

                            Document::query()->whereKey($records->modelKeys())->update(['owner_id' => $ownerId]);
                            DocumentShare::query()
                                ->whereIn('document_id', $records->modelKeys())
                                ->where('user_id', $ownerId)
                                ->delete();

                            Notification::make()
                                ->title('Ownership transferred')
                                ->body($records->count().' document(s) moved.')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->striped()
            ->emptyStateIcon(Heroicon::OutlinedDocumentText)
            ->emptyStateHeading('No documents yet')
            ->emptyStateDescription('Documents created in the app will show up here.');
    }
}
