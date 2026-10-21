<?php

namespace App\Filament\Resources\NewsletterSubscriberResource\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;

class SubscriberInfolist
{
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Subscriber Details')
                    ->schema([
                        TextEntry::make('email')
                            ->label('Email Address')
                            ->weight('bold'),

                        IconEntry::make('is_active')
                            ->label('Status')
                            ->boolean(),

                        TextEntry::make('created_at')
                            ->label('Joined Date & Time')
                            ->dateTime(),

                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime(),
                    ])->columns(2),
            ]);
    }
}
