<?php

namespace App\Filament\Resources\SlideResource\Schemas;

use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Grid;

class SlideInfolist
{
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Slide / Banner Full Details')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                ImageEntry::make('image')
                                    ->label('Banner Image')
                                    ->disk('public')
                                    ->columnSpan(1)
                                    ->square(),

                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('title')
                                            ->weight('bold')
                                            ->columnSpanFull(),

                                        TextEntry::make('subtitle')
                                            ->placeholder('No Subtitle Specified'),

                                        TextEntry::make('location')
                                            ->badge()
                                            ->color(fn(string $state): string => match ($state) {
                                                'hero' => 'info',
                                                'newsletter' => 'success',
                                                default => 'gray',
                                            }),
                                    ])
                                    ->columnSpan(2),
                            ]),

                        Section::make('Button & Redirect Settings')
                            ->schema([
                                TextEntry::make('button_text')
                                    ->label('CTA Button Text')
                                    ->placeholder('No Button'),

                                TextEntry::make('button_url')
                                    ->label('Redirect URL / Link')
                                    ->placeholder('#'),
                            ])->columns(2),

                        Section::make('System Info')
                            ->schema([
                                IconEntry::make('is_active')
                                    ->label('Visibility Status')
                                    ->boolean(),

                                TextEntry::make('sort_order')
                                    ->label('Display Order'),

                                TextEntry::make('description')
                                    ->label('Description / Text Content')
                                    ->columnSpanFull()
                                    ->placeholder('No Description Provided'),
                            ])->columns(2),
                    ]),
            ]);
    }
}
