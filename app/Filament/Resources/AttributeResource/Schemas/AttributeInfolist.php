<?php

namespace App\Filament\Resources\AttributeResource\Schemas;

use Filament\Infolists;
use Filament\Infolists\Infolist;

class AttributeInfolist
{
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Attribute Info')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('Attribute Name')
                            ->weight('bold'),

                        Infolists\Components\TextEntry::make('type')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'color' => 'info',
                                'text' => 'success',
                                default => 'gray',
                            }),
                    ])->columns(2),

                // අදාල Values ටික ලිස්ට් එකක් විදියට පෙන්වීම
                Infolists\Components\Section::make('Attribute Values')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('values')
                            ->label('') // උඩින් නමක් ඕන නෑ
                            ->schema([
                                Infolists\Components\Grid::make(2)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('value')
                                            ->label('Value Name'),

                                        // Color එකක් නම් පාට පෙන්නනවා
                                        Infolists\Components\ColorEntry::make('hex_color')
                                            ->label('Color Swatch')
                                            ->visible(fn($record) => !empty($record->hex_color)),
                                    ]),
                            ])
                            ->grid(3) // පේළියකට 3 ගානේ පෙන්නන්න
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
