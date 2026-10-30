<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Documents\DocumentResource;
use App\Models\Document;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentDocuments extends TableWidget
{
    protected static ?int $sort = 4;

    protected static ?string $heading = 'Recently edited';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => Document::query()->with('owner:id,name')->withCount('shares')->latest('updated_at'))
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5)
            ->columns([
                TextColumn::make('title')->weight('medium')->limit(60),
                TextColumn::make('owner.name')->label('Owner'),
                TextColumn::make('shares_count')
                    ->label('Shared with')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'primary' : 'gray'),
                TextColumn::make('updated_at')->label('Last edited')->since()->dateTimeTooltip(),
            ])
            ->recordUrl(fn (Document $record): string => DocumentResource::getUrl('view', ['record' => $record]))
            ->recordActions([
                Action::make('view')
                    ->label('View')
                    ->icon(Heroicon::OutlinedEye)
                    ->url(fn (Document $record): string => DocumentResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
