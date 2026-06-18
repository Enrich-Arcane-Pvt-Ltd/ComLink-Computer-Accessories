<?php

namespace App\Filament\Resources\CategoryResource\Schemas;

use Filament\Infolists;
use Filament\Infolists\Infolist;

class CategoryInfolist
{
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Category Info')
                    ->schema([
                        Infolists\Components\ImageEntry::make('image')
                            ->circular()
                            ->extraImgAttributes(['style' => 'background-color: white; padding: 2px;']),

                        Infolists\Components\TextEntry::make('name')->weight('bold'),

                        Infolists\Components\TextEntry::make('parent.name')
                            ->label('Parent Category')
                            ->placeholder('Main Category'),

                        Infolists\Components\IconEntry::make('is_visible')
                            ->label('Active Status')
                            ->boolean(),

                        Infolists\Components\TextEntry::make('description')
                            ->markdown()
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }
}
