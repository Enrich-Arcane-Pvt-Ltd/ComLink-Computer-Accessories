<?php

namespace App\Filament\Resources\TestimonialResource\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;

class TestimonialInfolist
{
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Testimonial Full Details')
                    ->schema([
                        ImageEntry::make('avatar')
                            ->label('Customer Avatar')
                            ->defaultImageUrl(url('https://ui-avatars.com/api/?background=random'))
                            ->circular(),

                        TextEntry::make('name')
                            ->weight('bold')
                            ->label('Customer Name'),

                        TextEntry::make('user.email')
                            ->label('Registered Email')
                            ->placeholder('Guest User / Form Submission'),

                        TextEntry::make('location')
                            ->placeholder('Not Specified'),

                        TextEntry::make('rating')
                            ->label('Rating Given')
                            ->formatStateUsing(fn (int $state): string => str_repeat('⭐', $state)),

                        IconEntry::make('is_active')
                            ->label('Approval Status')
                            ->boolean(),

                        TextEntry::make('quote')
                            ->label('Review / Quote')
                            ->columnSpanFull()
                            ->html(),
                    ])->columns(2),
            ]);
    }
}
