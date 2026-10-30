<?php

namespace App\Filament\Resources\Documents\RelationManagers;

use App\Enums\ShareRole;
use BackedEnum;
use App\Models\Document;
use App\Models\DocumentShare;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\Unique;

class SharesRelationManager extends RelationManager
{
    protected static string $relationship = 'shares';

    protected static ?string $title = 'People with access';

    protected static string|BackedEnum|null $icon = Heroicon::OutlinedUserGroup;

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        /** @var Document $ownerRecord */
        return (string) $ownerRecord->shares()->count();
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('user_id')
                ->label('Person')
                ->relationship(
                    'user',
                    'name',
                    fn (Builder $query) => $query->whereKeyNot($this->getOwnerRecord()->getAttribute('owner_id')),
                )
                ->searchable(['name', 'email'])
                ->preload()
                ->required()
                ->unique(
                    table: DocumentShare::class,
                    column: 'user_id',
                    ignoreRecord: true,
                    modifyRuleUsing: fn (Unique $rule) => $rule->where('document_id', $this->getOwnerRecord()->getKey()),
                )
                ->validationMessages(['unique' => 'This person already has access.'])
                ->disabledOn('edit'),
            ToggleButtons::make('role')
                ->label('Access')
                ->options(ShareRole::class)
                ->default(ShareRole::Viewer)
                ->inline()
                ->required(),
        ])->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user.name')
            ->modifyQueryUsing(fn (Builder $query) => $query->with('user:id,name,email'))
            ->columns([
                TextColumn::make('user.name')
                    ->label('Person')
                    ->description(fn (DocumentShare $record): string => $record->user->email)
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
            ->filters([
                SelectFilter::make('role')->options(ShareRole::class),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add person')
                    ->icon(Heroicon::OutlinedUserPlus)
                    ->modalHeading('Share this document'),
            ])
            ->recordActions([
                EditAction::make()->label('Change access')->modalHeading('Change access'),
                DeleteAction::make()->label('Revoke')->modalHeading('Revoke access'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    self::changeRoleBulkAction(ShareRole::Editor),
                    self::changeRoleBulkAction(ShareRole::Viewer),
                    DeleteBulkAction::make()->label('Revoke selected'),
                ]),
            ])
            ->emptyStateIcon(Heroicon::OutlinedLink)
            ->emptyStateHeading('Not shared yet')
            ->emptyStateDescription('Only the owner can open this document.');
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
            ->deselectRecordsAfterCompletion()
            ->successNotificationTitle('Access updated');
    }
}
