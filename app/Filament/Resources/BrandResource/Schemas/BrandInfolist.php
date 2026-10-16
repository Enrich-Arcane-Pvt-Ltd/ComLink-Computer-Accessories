<?php

namespace App\Filament\Resources\BrandResource\Schemas;

use Filament\Infolists;
use Filament\Infolists\Infolist;

class BrandInfolist
{
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Brand Details')
                    ->schema([
                        Infolists\Components\ImageEntry::make('logo')
                            ->label('Logo')
                            ->circular()
                            // View එකෙත් සුදු පාට Background එක ඕන නම්
                            ->extraImgAttributes(['style' => 'background-color: white; padding: 2px;']),

                        Infolists\Components\TextEntry::make('name')
                            ->label('Brand Name')
                            ->weight('bold'),

                        Infolists\Components\TextEntry::make('url')
                            ->label('Website')
                            ->url(fn ($state) => $state)
                            ->openUrlInNewTab()
                            ->color('primary'),

                        Infolists\Components\ColorEntry::make('primary_hex')
                            ->label('Theme Color'),

                        Infolists\Components\IconEntry::make('is_visible')
                            ->label('Status')
                            ->boolean(),

                        Infolists\Components\TextEntry::make('description')
                            ->markdown()
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }
}
