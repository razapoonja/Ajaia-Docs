<?php

namespace App\Filament\Resources\DocumentShares\Pages;

use App\Filament\Resources\DocumentShares\DocumentShareResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Icons\Heroicon;

class ManageDocumentShares extends ManageRecords
{
    protected static string $resource = DocumentShareResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New share')
                ->icon(Heroicon::OutlinedUserPlus),
        ];
    }
}
