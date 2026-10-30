<?php

namespace App\Filament\Resources\Documents\Schemas;

use App\Filament\Resources\Users\UserResource;
use App\Models\Document;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class DocumentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Content')
                    ->columnSpan(2)
                    ->schema([
                        TextEntry::make('content')
                            ->hiddenLabel()
                            ->html()
                            ->prose()
                            ->placeholder('This document is empty.'),
                    ]),
                Grid::make(1)
                    ->columnSpan(1)
                    ->schema([
                        Section::make('Overview')
                            ->schema([
                                TextEntry::make('title')
                                    ->weight('semibold'),
                                TextEntry::make('owner.name')
                                    ->label('Owner')
                                    ->icon(Heroicon::OutlinedUser)
                                    ->url(fn (Document $record): string => UserResource::getUrl('view', ['record' => $record->owner_id])),
                                TextEntry::make('owner.email')
                                    ->label('Owner email')
                                    ->icon(Heroicon::OutlinedEnvelope)
                                    ->copyable(),
                                TextEntry::make('shares_count')
                                    ->label('Shared with')
                                    ->state(fn (Document $record): int => $record->shares()->count())
                                    ->formatStateUsing(fn (int $state): string => $state === 1 ? '1 person' : "{$state} people")
                                    ->badge()
                                    ->color(fn (int $state): string => $state > 0 ? 'primary' : 'gray'),
                            ]),
                        Section::make('Activity')
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Created')
                                    ->dateTime('M j, Y · g:i A')
                                    ->icon(Heroicon::OutlinedCalendar),
                                TextEntry::make('updated_at')
                                    ->label('Last edited')
                                    ->since()
                                    ->dateTimeTooltip()
                                    ->icon(Heroicon::OutlinedClock),
                            ]),
                    ]),
            ]);
    }
}
