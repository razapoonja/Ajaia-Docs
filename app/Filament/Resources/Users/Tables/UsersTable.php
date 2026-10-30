<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount(['ownedDocuments', 'documentShares']))
            ->columns([
                TextColumn::make('name')
                    ->description(fn (User $record): string => $record->email)
                    ->searchable(['name', 'email'])
                    ->sortable()
                    ->weight('medium'),
                IconColumn::make('is_admin')
                    ->label('Admin')
                    ->boolean()
                    ->trueIcon(Heroicon::OutlinedShieldCheck)
                    ->falseIcon(Heroicon::OutlinedMinus)
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('owned_documents_count')
                    ->label('Owns')
                    ->badge()
                    ->color('primary')
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('document_shares_count')
                    ->label('Shared with them')
                    ->badge()
                    ->color('gray')
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Joined')
                    ->since()
                    ->dateTimeTooltip()
                    ->sortable(),
            ])
            ->defaultSort('name')
            ->filters([
                TernaryFilter::make('is_admin')
                    ->label('Role')
                    ->placeholder('Everyone')
                    ->trueLabel('Admins only')
                    ->falseLabel('Members only'),
                TernaryFilter::make('has_documents')
                    ->label('Documents')
                    ->placeholder('Any')
                    ->trueLabel('Owns documents')
                    ->falseLabel('Owns none')
                    ->queries(
                        true: fn (Builder $query) => $query->has('ownedDocuments'),
                        false: fn (Builder $query) => $query->doesntHave('ownedDocuments'),
                    ),
            ])
            ->checkIfRecordIsSelectableUsing(fn (User $record): bool => ! $record->is(auth()->user()))
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make()
                        ->hidden(fn (User $record): bool => $record->is(auth()->user()))
                        ->modalDescription('Their documents and shares are deleted too. This cannot be undone.'),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    self::adminBulkAction(true),
                    self::adminBulkAction(false),
                    DeleteBulkAction::make()
                        ->modalDescription('Their documents and shares are deleted too. This cannot be undone.'),
                ]),
            ])
            ->striped();
    }

    private static function adminBulkAction(bool $grant): BulkAction
    {
        return BulkAction::make($grant ? 'grantAdmin' : 'revokeAdmin')
            ->label($grant ? 'Grant admin access' : 'Revoke admin access')
            ->icon($grant ? Heroicon::OutlinedShieldCheck : Heroicon::OutlinedShieldExclamation)
            ->color($grant ? 'primary' : 'warning')
            ->requiresConfirmation()
            ->action(function (Collection $records, BulkAction $action) use ($grant): void {
                User::query()->whereKey($records->modelKeys())->update(['is_admin' => $grant]);
                $action->success();
            })
            ->successNotificationTitle($grant ? 'Admin access granted' : 'Admin access revoked')
            ->deselectRecordsAfterCompletion();
    }
}
