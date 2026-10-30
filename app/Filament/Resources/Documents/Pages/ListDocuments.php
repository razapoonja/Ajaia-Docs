<?php

namespace App\Filament\Resources\Documents\Pages;

use App\Filament\Resources\Documents\DocumentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListDocuments extends ListRecords
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('New document'),
        ];
    }

    /**
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'shared' => Tab::make('Shared')
                ->modifyQueryUsing(fn (Builder $query) => $query->has('shares')),
            'private' => Tab::make('Private')
                ->modifyQueryUsing(fn (Builder $query) => $query->doesntHave('shares')),
            'recent' => Tab::make('Edited this week')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('updated_at', '>=', now()->subWeek())),
        ];
    }
}
