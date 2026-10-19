<?php

namespace App\Filament\Resources\SupplierResource\Schemas;

use Filament\Infolists;
use Filament\Infolists\Infolist;

class SupplierInfolist
{
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Supplier Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('Supplier Name')
                            ->weight('bold')
                            ->icon('heroicon-o-building-storefront'),

                        Infolists\Components\TextEntry::make('contact_no')
                            ->label('Contact No')
                            ->icon('heroicon-o-phone')
                            ->placeholder('Not provided'),

                        Infolists\Components\TextEntry::make('email')
                            ->label('Email')
                            ->icon('heroicon-o-envelope')
                            ->placeholder('Not provided'),

                        Infolists\Components\TextEntry::make('address')
                            ->label('Address')
                            ->columnSpanFull()
                            ->placeholder('Not provided'),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Registration Date')
                            ->dateTime('M d, Y - h:i A'),

                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime('M d, Y - h:i A'),
                    ])->columns(2),
            ]);
    }
}
