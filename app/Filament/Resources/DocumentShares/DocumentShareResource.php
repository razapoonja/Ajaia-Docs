<?php

namespace App\Filament\Resources\DocumentShares;

use App\Filament\Resources\DocumentShares\Pages\ManageDocumentShares;
use App\Filament\Resources\DocumentShares\Schemas\DocumentShareForm;
use App\Filament\Resources\DocumentShares\Tables\DocumentSharesTable;
use App\Models\DocumentShare;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class DocumentShareResource extends Resource
{
    protected static ?string $model = DocumentShare::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShare;

    protected static string|UnitEnum|null $navigationGroup = 'Workspace';

    protected static ?string $navigationLabel = 'Shares';

    protected static ?string $modelLabel = 'share';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return DocumentShareForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DocumentSharesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageDocumentShares::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }
}
