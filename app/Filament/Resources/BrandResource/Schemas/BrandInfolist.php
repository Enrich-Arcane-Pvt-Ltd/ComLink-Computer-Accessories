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
                            ->state(fn (\App\Models\Brand $record): ?string => $record->logo_url)
                            ->defaultImageUrl(fn (\App\Models\Brand $record): string => $record->getInitialsAvatarSvgUrl())
                            ->extraImgAttributes(['style' => 'background-color: white; padding: 2px;']),

                        Infolists\Components\TextEntry::make('name')
                            ->label('Brand Name')
                            ->weight('bold'),

                        Infolists\Components\IconEntry::make('is_visible')
                            ->label('Status')
                            ->boolean(),
                    ])->columns(2),
            ]);
    }
}
