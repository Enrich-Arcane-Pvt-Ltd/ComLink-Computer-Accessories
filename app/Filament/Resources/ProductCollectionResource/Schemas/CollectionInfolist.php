<?php

namespace App\Filament\Resources\ProductCollectionResource\Schemas;

use Filament\Infolists;
use Filament\Infolists\Infolist;

class CollectionInfolist
{
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Collection Info')
                    ->schema([
                        Infolists\Components\ImageEntry::make('image')
                            ->circular()
                            ->extraImgAttributes(['style' => 'background-color: white; padding: 2px;']),

                        Infolists\Components\TextEntry::make('title')
                            ->weight('bold')
                            ->label('Title'),

                        Infolists\Components\TextEntry::make('slug')
                            ->label('Slug / URL Key')
                            ->color('gray'),

                        Infolists\Components\IconEntry::make('is_visible')
                            ->label('Visibility')
                            ->boolean(),

                        Infolists\Components\IconEntry::make('is_featured')
                            ->label('Featured')
                            ->boolean()
                            ->color('warning'),

                        Infolists\Components\TextEntry::make('description')
                            ->markdown()
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }
}
