<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Profile')
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')->weight('semibold'),
                        TextEntry::make('email')
                            ->icon(Heroicon::OutlinedEnvelope)
                            ->copyable(),
                        IconEntry::make('is_admin')
                            ->label('Admin access')
                            ->boolean(),
                        TextEntry::make('created_at')
                            ->label('Joined')
                            ->date('M j, Y')
                            ->icon(Heroicon::OutlinedCalendar),
                    ]),
                Section::make('Activity')
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('owned_documents_count')
                            ->label('Documents owned')
                            ->state(fn (User $record): int => $record->ownedDocuments()->count())
                            ->badge()
                            ->color('primary'),
                        TextEntry::make('document_shares_count')
                            ->label('Shared with them')
                            ->state(fn (User $record): int => $record->documentShares()->count())
                            ->badge()
                            ->color('gray'),
                        TextEntry::make('updated_at')
                            ->label('Profile updated')
                            ->since()
                            ->dateTimeTooltip(),
                    ]),
            ]);
    }
}
