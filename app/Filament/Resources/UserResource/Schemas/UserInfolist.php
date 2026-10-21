<?php

namespace App\Filament\Resources\UserResource\Schemas;

use Filament\Infolists;
use Filament\Infolists\Infolist;

class UserInfolist
{
    public static function configure(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make()
                    ->schema([
                        Infolists\Components\Split::make([
                            // 1. වම් පැත්තේ විස්තර (Grid Layout)
                            Infolists\Components\Grid::make(2)
                                ->schema([
                                    Infolists\Components\Group::make([
                                        Infolists\Components\TextEntry::make('customer_id')
                                            ->label('Customer ID')
                                            ->weight('bold')
                                            ->copyable(),

                                        Infolists\Components\TextEntry::make('name')
                                            ->label('Full Name')
                                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large),

                                        Infolists\Components\TextEntry::make('email')
                                            ->icon('heroicon-m-envelope')
                                            ->copyable(),
                                    ]),

                                    Infolists\Components\Group::make([
                                        Infolists\Components\TextEntry::make('phone')
                                            ->label('Primary Phone')
                                            ->icon('heroicon-m-phone'),

                                        Infolists\Components\TextEntry::make('secondary_phone')
                                            ->label('Secondary Phone')
                                            ->placeholder('N/A')
                                            ->icon('heroicon-m-phone'),
                                    ]),
                                ]),

                            // 2. දකුණු පැත්තේ Avatar සහ Badges
                            Infolists\Components\Section::make()
                                ->schema([
                                    Infolists\Components\ImageEntry::make('avatar_url')
                                        ->hiddenLabel()
                                        ->circular()
                                        ->defaultImageUrl(fn ($record) => $record->getFilamentAvatarUrl())
                                        ->height(100), // ලොකුවට පෙන්නන්න

                                    Infolists\Components\TextEntry::make('role')
                                        ->badge()
                                        ->color(fn (string $state): string => $state === 'admin' ? 'danger' : 'info')
                                        ->alignCenter(),

                                    Infolists\Components\TextEntry::make('status')
                                        ->badge()
                                        ->color(fn (string $state): string => match ($state) {
                                            'active' => 'success',
                                            'blocked' => 'danger',
                                            default => 'gray',
                                        })
                                        ->alignCenter(),
                                ])
                                ->grow(false), // මේක පොඩියට තියාගන්න (Side bar වගේ)
                        ])->from('md'), // Mobile වලදී යටට වැටෙනවා, Desktop වලදී දෙපැත්තට
                    ]),
            ]);
    }
}
