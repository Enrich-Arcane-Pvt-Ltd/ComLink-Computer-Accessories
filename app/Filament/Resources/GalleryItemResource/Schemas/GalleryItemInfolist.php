<?php

namespace App\Filament\Resources\GalleryItemResource\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;

class GalleryItemInfolist
{
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Gallery Item Details')
                    ->schema([
                        ImageEntry::make('image')
                            ->label('Full Image')
                            ->disk('public')
                            ->columnSpanFull(),

                        TextEntry::make('title')
                            ->label('Title')
                            ->placeholder('No Title Provided')
                            ->weight('bold'),

                        TextEntry::make('external_link')
                            ->label('External Link (Redirect)')
                            ->placeholder('Direct View only'),

                        IconEntry::make('is_visible')
                            ->label('Visible in Main Gallery')
                            ->boolean(),

                        IconEntry::make('is_featured_home')
                            ->label('Featured on Home Page')
                            ->boolean(),

                        TextEntry::make('sort_order')
                            ->label('Display Order'),
                    ])->columns(2),
            ]);
    }
}
