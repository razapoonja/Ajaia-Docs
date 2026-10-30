<?php

namespace App\Filament\Resources\DocumentShares\Tables;

use App\Enums\ShareRole;
use App\Filament\Resources\Documents\DocumentResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\DocumentShare;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class DocumentSharesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with([
                'document:id,title,owner_id',
                'document.owner:id,name',
                'user:id,name,email',
            ]))
            ->columns([
                TextColumn::make('document.title')
                    ->label('Document')
                    ->description(fn (DocumentShare $record): string => 'Owned by '.$record->document->owner->name)
                    ->url(fn (DocumentShare $record): string => DocumentResource::getUrl('view', ['record' => $record->document_id]))
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('user.name')
                    ->label('Shared with')
                    ->description(fn (DocumentShare $record): string => $record->user->email)
                    ->url(fn (DocumentShare $record): string => UserResource::getUrl('view', ['record' => $record->user_id]))
                    ->searchable(['name', 'email'])
                    ->sortable(),
                TextColumn::make('role')
                    ->label('Access')
                    ->badge()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Granted')
                    ->since()
                    ->dateTimeTooltip()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->groups([
                Group::make('document.title')->label('Document')->collapsible(),
                Group::make('user.name')->label('Person')->collapsible(),
                Group::make('role')->label('Access')->collapsible(),
            ])
            ->filters([
                SelectFilter::make('role')->label('Access')->options(ShareRole::class),
                SelectFilter::make('document_id')
                    ->label('Document')
                    ->relationship('document', 'title')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('user_id')
                    ->label('Person')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()->label('Change access'),
                    DeleteAction::make()->label('Revoke'),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    self::changeRoleBulkAction(ShareRole::Editor),
                    self::changeRoleBulkAction(ShareRole::Viewer),
                    DeleteBulkAction::make()->label('Revoke selected'),
                ]),
            ])
            ->striped()
            ->emptyStateIcon(Heroicon::OutlinedShare)
            ->emptyStateHeading('No shares yet')
            ->emptyStateDescription('When an owner shares a document it will be listed here.');
    }

    private static function changeRoleBulkAction(ShareRole $role): BulkAction
    {
        return BulkAction::make('makeRole'.ucfirst($role->value))
            ->label('Set to "'.$role->getLabel().'"')
            ->icon($role->getIcon())
            ->action(function (Collection $records, BulkAction $action) use ($role): void {
                DocumentShare::query()->whereKey($records->modelKeys())->update(['role' => $role->value]);
                $action->success();
            })
            ->successNotificationTitle('Access updated')
            ->deselectRecordsAfterCompletion();
    }
}
